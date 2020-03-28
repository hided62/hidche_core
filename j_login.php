<?php
namespace sammo;

require(__DIR__.'/vendor/autoload.php');
require(__DIR__.'/oauth_kakao/lib.join.php');

use \kakao\Kakao_REST_API_Helper as Kakao_REST_API_Helper;

$RootDB = RootDB::db();
$session = Session::getInstance();
if($session->isLoggedIn()){
    $session->logout();
}

$username = mb_strtolower(Util::getReq('username'), 'utf-8');
$password = Util::getReq('password');

if(!$username || !$password){
    Json::die([
        'result'=>false,
        'reqOTP'=>false,
        'reason'=>'올바르지 않은 입력입니다.'
    ]);
}

function kakaoOAuthCheck(array $userInfo) : ?array {

    if(!\kakao\KakaoKey::REST_KEY){
        return [false, '카카오 API 앱이 등록되지 않았습니다. 관리자에게 문의해 주세요.'];
    }

    $oauthID = $userInfo['oauth_id'];
    $oauthInfo = Json::decode($userInfo['oauth_info'])??[];
    if(!$oauthInfo){
        return [false, 'OAuth 정보가 보관되어 있지 않습니다. 카카오 로그인을 수행해 주세요.'];
    }
    
    $accessToken = $oauthInfo['accessToken']??null;
    $refreshToken = $oauthInfo['refreshToken']??null;
    $accessTokenValidUntil = $oauthInfo['accessTokenValidUntil']??null;
    $refreshTokenValidUntil = $oauthInfo['refreshTokenValidUntil']??null;
    $OTPValue = $oauthInfo['OTPValue']??null;
    $OTPTrialUntil = $oauthInfo['OTPTrialUntil']??null;
    $tokenValidUntil = $userInfo['token_valid_until'];

    if(!$accessToken || !$refreshToken || !$accessTokenValidUntil || !$refreshTokenValidUntil){
        return [false, 'OAuth 정보가 보관되어 있지 않습니다. 카카오 로그인을 수행해 주세요.'];
    }

    $now = TimeUtil::now();

    if($now > $refreshTokenValidUntil){
        return [false, '로그인 토큰이 만료되었습니다. 카카오 로그인을 수행해 주세요.'];
    }

    if($now > $accessTokenValidUntil){
        $apiHelper = new Kakao_REST_API_Helper($accessToken);
        $refreshResult = $apiHelper->refresh_access_token($refreshToken);
        if(!$refreshResult){
            return [false, '로그인 토큰 자동 갱신을 실패했습니다. 카카오 로그인을 수행해 주세요.'];
        }

        $accessToken = $refreshResult['access_token']??null;

        if(!$accessToken){
            trigger_error("refreshToken 에러 ".Json::encode($refreshResult).",".$refreshToken.",".substr(\kakao\KakaoKey::REST_KEY, 0, 6), E_USER_NOTICE);
            return [false, '로그인 토큰 자동 갱신을 실패했습니다. 카카오 로그인을 수행해 주세요.'];
        }
        $accessTokenValidUntil = TimeUtil::nowAddSeconds($refreshResult['expires_in']);

        $oauthInfo['accessToken'] = $accessToken;
        $oauthInfo['accessTokenValidUntil'] = $accessTokenValidUntil;

        $refreshToken = $refreshResult['refresh_token']??null;
        if($refreshToken){
            $refreshTokenValidUntil = TimeUtil::nowAddSeconds($refreshResult['refresh_token_expires_in']);

            $oauthInfo['refreshToken'] = $refreshToken;
            $oauthInfo['refresh_token_expires_in'] = $refreshTokenValidUntil;
        }
        
        RootDB::db()->update('member', [
            'oauth_info'=>Json::encode($oauthInfo)
        ], 'no=%i', $userInfo['no']);
    }

    if($tokenValidUntil && $now <= $tokenValidUntil){
        return null;
    }

    //인증 시스템 가동
    $session = Session::getInstance();
    $session->access_token = $accessToken;
    $session->expires = $accessTokenValidUntil;
    $session->refresh_token = $refreshToken;
    $session->refresh_token_expires = $refreshTokenValidUntil;

    if(!createOTPbyUserNO($userInfo['no'])){
        return [false, '인증 코드를 보내는데 실패했습니다.'];
    }

    return [true, '인증 코드를 입력해주세요'];
}

$userInfo = $RootDB->queryFirstRow(
    'SELECT `no`, `id`, `name`, `grade`, `delete_after`, `acl`, oauth_id, oauth_type, oauth_info, token_valid_until '.
    'from member where id=%s_username AND '.
    'pw=sha2(concat(salt, %s_password, salt), 512)',[
        'username'=>$username,
        'password'=>$password
]);

if(!$userInfo){
    Json::die([
        'result'=>false,
        'reqOTP'=>false,
        'reason'=>'아이디나 비밀번호가 올바르지 않습니다.'
    ]);
}

$canLogin = $RootDB->queryFirstField('SELECT `LOGIN` FROM `system` WHERE `NO` = 1');
if($canLogin != 'Y' && $userInfo['grade'] < 5){
    Json::die([
        'result'=>false,
        'reason'=>'현재는 로그인이 금지되어있습니다!'
    ]);
}

$nowDate = TimeUtil::now();
if($userInfo['delete_after']){
    if($userInfo['delete_after'] < $nowDate){
        $RootDB->delete('member', 'no=%i', $userInfo['no']);
        Json::die([
            'result'=>false,
            'reqOTP'=>false,
            'reason'=>"기간 만기로 삭제되었습니다. 재 가입을 시도해주세요."
        ]);
    }
    else{
        Json::die([
            'result'=>false,
            'reqOTP'=>false,
            'reason'=>"삭제 요청된 계정입니다.[{$userInfo['delete_after']}]"
        ]);
    }
    
}

$RootDB->insert('member_log',[
    'member_no'=>$userInfo['no'],
    'action_type'=>'login',
    'action'=>Json::encode([
        'ip'=>Util::get_client_ip(true),
        'type'=>'plain'
    ])
]);

if($userInfo['oauth_type'] == 'KAKAO'){
    $oauthFailResult = kakaoOAuthCheck($userInfo);
    if($oauthFailResult !== null){
        $session->login($userInfo['no'], $userInfo['id'], $userInfo['grade'], true, $userInfo['token_valid_until'], Json::decode($userInfo['acl']??'{}'));
        [$oauthReqOTP, $oauthFailReason] = $oauthFailResult;
        Json::die([
            'result'=>false,
            'reqOTP'=>$oauthReqOTP,
            'reason'=>$oauthFailReason
        ]);        
    }
}


$session->login($userInfo['no'], $userInfo['id'], $userInfo['grade'], false, $userInfo['token_valid_until'], Json::decode($userInfo['acl']??'{}'));
Json::die([
    'result'=>true,
    'reqOTP'=>false,
    'reason'=>'로그인 되었습니다.'
]);