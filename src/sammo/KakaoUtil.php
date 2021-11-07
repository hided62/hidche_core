<?php

namespace sammo;

use kakao\Kakao_REST_API_Helper;

class KakaoUtil
{
    private function __construct()
    {
    }

    static function checkUsernameDup($username)
    {
        if (!$username) {
            return '계정명을 입력해주세요';
        }

        $username = mb_strtolower($username, 'utf-8');
        $length = strlen($username);
        if ($length < 4 || $length > 64) {
            return '적절하지 않은 길이입니다.';
        }

        $cnt = RootDB::db()->queryFirstField('SELECT count(no) FROM member WHERE `id` = %s LIMIT 1', $username);
        if ($cnt != 0) {
            return '이미 사용중인 계정명입니다';
        }
        return true;
    }

    static function checkNicknameDup($nickname)
    {
        if (!$nickname) {
            return '닉네임을 입력해주세요';
        }

        $length = mb_strwidth($nickname, 'utf-8');
        if ($length < 1 || $length > 18) {
            return '적절하지 않은 길이입니다.';
        }

        $cnt = RootDB::db()->queryFirstField('SELECT count(no) FROM member WHERE `name` = %s LIMIT 1', $nickname);
        if ($cnt != 0) {
            return '이미 사용중인 닉네임입니다';
        }
        return true;
    }


    static function checkEmailDup($email)
    {
        if (!$email) {
            return '이메일을 입력해주세요';
        }

        $length = strlen($email);
        if ($length < 1 || $length > 64) {
            return '적절하지 않은 길이입니다.';
        }

        $userInfo = RootDB::db()->queryFirstField('SELECT `no`, `delete_after` FROM member WHERE `email` = %s LIMIT 1', $email);
        if ($userInfo) {
            if (!$userInfo['delete_after']) {
                return '이미 사용중인 이메일입니다. 관리자에게 문의해주세요.';
            }

            if ($userInfo['delete_after'] >= $userInfo) {
                return "삭제 요청된 계정입니다.[{$userInfo['delete_after']}]";
            }

            //$userInfo['delete_after'] < $userInfo
            RootDB::db()->delete('member', 'no=%i', $userInfo['no']);
        }
        return true;
    }

    static function createOTPbyUserNO(int $userNo): bool
    {
        $userInfo = RootDB::db()->queryFirstRow('SELECT oauth_info FROM member WHERE no=%i', $userNo);
        if (!$userInfo) {
            return false;
        }

        $oauthInfo = Json::decode($userInfo['oauth_info']);
        if (!$oauthInfo) {
            return false;
        }

        $accessToken = $oauthInfo['accessToken'];
        $OTPValue = $oauthInfo['OTPValue'] ?? null;
        $OTPTrialUntil = $oauthInfo['OTPTrialUntil'] ?? null;

        $now = TimeUtil::now();


        if ($OTPTrialUntil && $OTPValue && $OTPTrialUntil > $now) {
            return true;
        }

        [$OTPValue, $OTPTrialUntil] = static::createOTP($accessToken);

        if (!$OTPValue) {
            return false;
        }

        $oauthInfo['OTPValue'] = $OTPValue;
        $oauthInfo['OTPTrialUntil'] = $OTPTrialUntil;
        $oauthInfo['OTPTrialCount'] = 3;

        RootDB::db()->update('member', [
            'oauth_info' => Json::encode($oauthInfo)
        ], 'no=%i', $userNo);

        return true;
    }

    static function createOTP(string $accessToken): ?array
    {
        $restAPI = new Kakao_REST_API_Helper($accessToken);

        $OTPValue = Util::randRangeInt(1000, 9999);
        $OTPTrialUntil = TimeUtil::nowAddSeconds(180);

        $sendResult = $restAPI->talk_to_me_default([
            "object_type" => "text",
            "text" => "인증 코드는 $OTPValue 입니다. $OTPTrialUntil 이내에 입력해주세요.",
            "link" => [
                "web_url" => ServConfig::getServerBasepath(),
                "mobile_web_url" => ServConfig::getServerBasepath()
            ],
            "button_title" => "로그인 페이지 열기"
        ]);
        $sendResult['code'] = Util::array_get($sendResult['code'], 0);
        if ($sendResult['code'] < 0) {
            return null;
        }

        return [$OTPValue, $OTPTrialUntil];
    }

    static function kakaoOAuthCheck(array $userInfo): ?array
    {

        if (!\kakao\KakaoKey::REST_KEY) {
            return [false, '카카오 API 앱이 등록되지 않았습니다. 관리자에게 문의해 주세요.'];
        }

        $oauthID = $userInfo['oauth_id'];
        $oauthInfo = Json::decode($userInfo['oauth_info']) ?? [];
        if (!$oauthInfo) {
            return [false, 'OAuth 정보가 보관되어 있지 않습니다. 카카오 로그인을 수행해 주세요.'];
        }

        $accessToken = $oauthInfo['accessToken'] ?? null;
        $refreshToken = $oauthInfo['refreshToken'] ?? null;
        $accessTokenValidUntil = $oauthInfo['accessTokenValidUntil'] ?? null;
        $refreshTokenValidUntil = $oauthInfo['refreshTokenValidUntil'] ?? null;
        $OTPValue = $oauthInfo['OTPValue'] ?? null;
        $OTPTrialUntil = $oauthInfo['OTPTrialUntil'] ?? null;
        $tokenValidUntil = $userInfo['token_valid_until'];

        if (!$accessToken || !$refreshToken || !$accessTokenValidUntil || !$refreshTokenValidUntil) {
            return [false, 'OAuth 정보가 보관되어 있지 않습니다. 카카오 로그인을 수행해 주세요.'];
        }

        $now = TimeUtil::now();

        if ($now > $refreshTokenValidUntil) {
            return [false, '로그인 토큰이 만료되었습니다. 카카오 로그인을 수행해 주세요.'];
        }

        if ($now > $accessTokenValidUntil) {
            $apiHelper = new Kakao_REST_API_Helper($accessToken);
            $refreshResult = $apiHelper->refresh_access_token($refreshToken);
            if (!$refreshResult) {
                return [false, '로그인 토큰 자동 갱신을 실패했습니다. 카카오 로그인을 수행해 주세요.'];
            }

            $accessToken = $refreshResult['access_token'] ?? null;

            if (!$accessToken) {
                trigger_error("refreshToken 에러 " . Json::encode($refreshResult) . "," . $refreshToken . "," . substr(\kakao\KakaoKey::REST_KEY, 0, 6), E_USER_NOTICE);
                return [false, '로그인 토큰 자동 갱신을 실패했습니다. 카카오 로그인을 수행해 주세요.'];
            }
            $accessTokenValidUntil = TimeUtil::nowAddSeconds($refreshResult['expires_in']);

            $oauthInfo['accessToken'] = $accessToken;
            $oauthInfo['accessTokenValidUntil'] = $accessTokenValidUntil;

            $refreshToken = $refreshResult['refresh_token'] ?? null;
            if ($refreshToken) {
                $refreshTokenValidUntil = TimeUtil::nowAddSeconds($refreshResult['refresh_token_expires_in']);

                $oauthInfo['refreshToken'] = $refreshToken;
                $oauthInfo['refresh_token_expires_in'] = $refreshTokenValidUntil;
            }

            RootDB::db()->update('member', [
                'oauth_info' => Json::encode($oauthInfo)
            ], 'no=%i', $userInfo['no']);
        }

        if ($tokenValidUntil && $now <= $tokenValidUntil) {
            return null;
        }

        //인증 시스템 가동
        $session = Session::getInstance();
        $session->access_token = $accessToken;
        $session->expires = $accessTokenValidUntil;
        $session->refresh_token = $refreshToken;
        $session->refresh_token_expires = $refreshTokenValidUntil;

        if (!createOTPbyUserNO($userInfo['no'])) {
            return [false, '인증 코드를 보내는데 실패했습니다.'];
        }

        return [true, '인증 코드를 입력해주세요'];
    }
}
