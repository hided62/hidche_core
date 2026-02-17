<?php

namespace sammo\API\Login;

use sammo\Session;
use DateTimeInterface;
use sammo\BaseAPI;
use sammo\Enums\APIRecoveryType;
use sammo\Json;
use sammo\KakaoUtil;
use sammo\RootDB;
use sammo\TimeUtil;
use sammo\Util;
use sammo\Validator;

use function sammo\logError;

class LoginByToken extends LoginByID
{
    static array $sensitiveArgs = ['hashedToken'];

    public function getRequiredSessionMode(): int
    {
        return \sammo\BaseAPI::NO_LOGIN;
    }

    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v
            ->rule('required', [
                'token_id',
                'hashedToken'
            ]);

        if (!$v->validate()) {
            return $v->errorStr();
        }
        return null;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $loginNonce = $session->loginNonce ?? null;
        $loginNonceExpired = $session->loginNonceExpired ?? null;
        if (!is_string($loginNonce) || !is_string($loginNonceExpired)) {
            logError(
                'AutoLoginProcedureError',
                Json::encode([
                    'phase' => 'missing_nonce',
                    'sid' => session_id(),
                    'cookie_sid' => $_COOKIE[session_name()] ?? null,
                    'checked_at' => TimeUtil::now(true),
                    'host' => gethostname() ?: php_uname('n'),
                    'php' => PHP_VERSION,
                    'session_status' => session_status(),
                    'session_save_handler' => ini_get('session.save_handler') ?: null,
                    'session_save_path' => ini_get('session.save_path') ?: null,
                    'session_use_strict_mode' => ini_get('session.use_strict_mode') ?: null,
                    'session_cookie_samesite' => ini_get('session.cookie_samesite') ?: null,
                    'session_cookie_secure' => ini_get('session.cookie_secure') ?: null,
                    'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
                    'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'token_id' => $this->args['token_id'] ?? null,
                    'nonce_type' => gettype($loginNonce),
                    'nonce_expired_type' => gettype($loginNonceExpired),
                    'reqnonce_context' => $session->loginNonceDebug ?? null,
                ]),
                __FILE__,
                []
            );
            return '자동 로그인: 절차 오류';
        }

        $RootDB = RootDB::db();
        if ($session->isLoggedIn()) {
            $session->logout();
        }

        $token_id = $this->args['token_id'];

        $token_info = $RootDB->queryFirstRow('SELECT * FROM login_token WHERE id = %i', $token_id);
        if (!$token_info) {
            return [
                'result' => false,
                'silent' => true,
                'reason' => 'failed'
            ];
        }

        $hashedToken = $this->args['hashedToken'];
        $tokenAnswer = hash('sha512', "{$token_info['base_token']}{$loginNonce}");
        if (strtolower($hashedToken) != strtolower($tokenAnswer)) {
            return [
                'result' => false,
                'silent' => true,
                'reason' => 'failed'
            ];
        }

        $userInfo = $RootDB->queryFirstRow(
            'SELECT `no`, `id`, `name`, `grade`, `delete_after`, `acl`, oauth_id, oauth_type, oauth_info, token_valid_until ' .
                'from member where NO = %i',
            $token_info['user_id']
        );
        if(!$userInfo){
            return '자동 로그인: 올바른 계정이 아닙니다.';
        }

        $canLogin = $RootDB->queryFirstField('SELECT `LOGIN` FROM `system` WHERE `NO` = 1');
        if ($canLogin != 'Y' && $userInfo['grade'] < 5) {
            return '자동 로그인: 현재는 로그인이 금지되어있습니다!';
        }

        $nowDate = TimeUtil::now();
        if ($userInfo['delete_after']) {
            if ($userInfo['delete_after'] < $nowDate) {
                $RootDB->delete('member', 'no=%i', $userInfo['no']);
                return [
                    'result' => false,
                    'silent' => true,
                    'reason' => "기간 만기"
                ];
            } else {
                return [
                    'result' => false,
                    'silent' => true,
                    'reason' => "삭제 요청된 계정[{$userInfo['delete_after']}]"
                ];
            }
        }

        $RootDB->insert('member_log', [
            'member_no' => $userInfo['no'],
            'action_type' => 'login',
            'action' => Json::encode([
                'ip' => Util::get_client_ip(true),
                'type' => 'auto'
            ])
        ]);

        if ($userInfo['oauth_type'] == 'KAKAO') {
            $oauthFailResult = KakaoUtil::kakaoOAuthCheck($userInfo);
            if ($oauthFailResult !== null) {
                $session->login($userInfo['no'], $userInfo['name'], $userInfo['grade'], true, $userInfo['token_valid_until'], null, Json::decode($userInfo['acl'] ?? '{}'));
                [$oauthReqOTP, $oauthFailReason] = $oauthFailResult;
                $RootDB->delete(
                    'login_token',
                    'id = %i', $token_id
                );
                return $oauthFailReason;
            }
        }

        $session->login($userInfo['no'], $userInfo['name'], $userInfo['grade'], false, $userInfo['token_valid_until'], $token_id, Json::decode($userInfo['acl'] ?? '{}'));
        $this->scrubToken($userInfo['no']);

        $nextDate = TimeUtil::nowAddDays(2);
        if($nextDate < $token_info['expire_date']){
            return [
                'result' => true,
                'silent' => true,
                'reason' => 'success'
            ];
        }

        $RootDB->delete(
            'login_token',
            'id = %i', $token_id
        );
        $nextToken = $this->addToken($userInfo['no']);
        return [
            'result' => true,
            'silent' => true,
            'nextToken' => $nextToken,
            'reason' => 'success'
        ];
    }
}
