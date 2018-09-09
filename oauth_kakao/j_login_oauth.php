<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');
require('lib.join.php');
use \kakao\Kakao_REST_API_Helper as Kakao_REST_API_Helper;

$RootDB = RootDB::db();
$session = Session::getInstance();
if($session->isLoggedIn()){
    $session->logout();
}

$canLogin = RootDB::db()->queryFirstField('SELECT `LOGIN` FROM `system` WHERE `NO` = 1');
if($canLogin != 'Y'){
    Json::die([
        'result'=>false,
        'reqOTP'=>false,
        'reason'=>'현재는 로그인이 금지되어있습니다!',
        'noRetry'=>true
    ]);
}

$now = TimeUtil::DatetimeNow();

$access_token = $session->access_token;
$expires = $session->expires;
$refresh_token = $session->refresh_token;
$refresh_token_expires = $session->refresh_token_expires;
$email = $session->kaccount_email;


if(!$access_token || !$expires){
    Json::die([
        'result'=>false,
        'reqOTP'=>false,
        'reason'=>'카카오로그인이 이루어지지 않았습니다.'
    ]);
}

//TODO: join과 login의 동작이 비슷하다. helper class로 묶자.
$restAPI = new Kakao_REST_API_Helper($access_token);

if($expires < $now && (!$refresh_token || ($refresh_token_expires < $now))){
    Json::die([
        'result'=>false,
        'reqOTP'=>false,
        'reason'=>'로그인 토큰 만료.'.$refresh_token_expires.' 다시 카카오로그인을 수행해주세요.'
    ]);
}

if($expires < $now){
    $session->kaccount_email = null;
    $email = null;

    $result = $restAPI->refresh_access_token($refresh_token);
    if(!isset($refresh_token)){
        Json::die([
            'result'=>false,
            'reqOTP'=>false,
            'reason'=>'카카오 로그인 과정 중 추가 갱신 절차를 실패했습니다'
        ]);
    }

    $access_token = $result['access_token'];
    $expires = TimeUtil::DatetimeFromNowSecond($result['expires_in']);
    if(isset($result['refresh_token'])){
        $refresh_token = Util::array_get($result['refresh_token']);
        $refresh_token_expires = TimeUtil::DatetimeFromNowSecond($result['refresh_token_expires_in']);
    }
}


if(!$email){
    $me = $restAPI->meWithEmail();
    $me['code'] = Util::array_get($me['code'], 0);
    if ($me['code']< 0) {
        Json::die([
            'result'=>false,
            'reqOTP'=>false,
            'reason'=>'카카오로그인이 정상적으로 수행되지 않았습니다.'
        ]);
    }

    $kakao_account = $me['kakao_account']??null;
    if (!$kakao_account) {
        Json::die([
            'result'=>false,
            'reqOTP'=>false,
            'reason'=>'카카오 로그인 정보를 제대로 받아오지 못했습니다.'
        ]);
    }

    if(!($kakao_account['has_email']??false)){
        Json::die([
            'result'=>false,
            'reqOTP'=>false,
            'reason'=>'이메일 정보 공유를 허락해 주셔야 합니다.',
        ]);
    }

    $validEmail = $kakao_account['is_email_valid']??false;
    $verifiedEmail = $kakao_account['is_email_verified']??false;

    if(!$validEmail || !$verifiedEmail){
        $restAPI->unlink();
        Json::die([
            'result'=>false,
            'reqOTP'=>false,
            'reason'=>'카카오 계정 이메일이 아직 인증되지 않았습니다',
        ]);
    }
    
    $email = $kakao_account['email'];
    $session->kaccount_email = $email;
}


$userInfo = $RootDB->queryFirstRow(
    'SELECT `no`, `id`, `name`, `grade`, `delete_after`, `acl`, oauth_info from member where email=%s',$email);

if(!$userInfo){
    $restAPI->unlink();
    $session->access_token = null;
    Json::die([
        'result'=>false,
        'reqOTP'=>false,
        'reason'=>'카카오로그인에 해당하는 계정이 없습니다. 재 가입을 시도해주세요.',
        'aux'=>$session->tmpx,
    ]);
}

if($userInfo['delete_after']){
    if($userInfo['delete_after'] < $now){
        $restAPI->unlink();
        $session->access_token = null;
        $RootDB->delete('member', 'no=%i', $userInfo['no']);
        Json::die([
            'result'=>false,
            'reqOTP'=>false,
            'reason'=>"기간 만기로 삭제되었습니다. 재 가입을 시도해주세요.",
        ]);
    }
    else{
        $restAPI->unlink();
        $session->access_token = null;
        Json::die([
            'result'=>false,
            'reqOTP'=>false,
            'reason'=>"삭제 요청된 계정입니다.[{$userInfo['delete_after']}]"
        ]);
    }
    
}

$oauthInfo = Json::decode($userInfo['oauth_info'])??[];
$oauthInfo['accessToken'] = $access_token;
$oauthInfo['refreshToken'] = $refresh_token;
$oauthInfo['accessTokenValidUntil'] = $expires;
$oauthInfo['refreshTokenValidUntil'] = $refresh_token_expires;

RootDB::db()->update('member', [
    'oauth_info'=>Json::encode($oauthInfo)
], 'no=%i', $userInfo['no']);


$OTPValidUntil = $oauthInfo['OTPValidUntil']??null;

if(!$OTPValidUntil || $OTPValidUntil < $now){
    if(!createOTPbyUserNO($userInfo['no'])){
        Json::die([
            'result'=>false,
            'reqOTP'=>false,
            'reason'=>'인증 코드를 보내는데 실패했습니다.'
        ]);        
    }
    $session->login($userInfo['no'], $userInfo['id'], $userInfo['grade'], true, Json::decode($userInfo['acl']??'{}'));
    Json::die([
        'result'=>false,
        'reqOTP'=>true,
        'reason'=>'인증 코드를 입력해주세요.'
    ]);
}


$RootDB->insert('member_log',[
    'member_no'=>$userInfo['no'],
    'action_type'=>'login',
    'action'=>Json::encode([
        'ip'=>Util::get_client_ip(true),
        'type'=>'kakao'
    ])
]);

$session->login($userInfo['no'], $userInfo['id'], $userInfo['grade'], false, Json::decode($userInfo['acl']??'{}'));
Json::die([
    'result'=>true,
    'reqOTP'=>false,
    'reason'=>'로그인 되었습니다.'
]);