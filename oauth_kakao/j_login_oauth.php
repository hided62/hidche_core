<?php
namespace sammo;

require('_common.php');

use \kakao\Kakao_REST_API_Helper as Kakao_REST_API_Helper;

$session = Session::Instance();
if($session->isLoggedIn()){
    $session->logout();
}

$canLogin = RootDB::db()->queryFirstField('SELECT `LOGIN` FROM `SYSTEM` WHERE `NO` = 1');
if($canLogin != 'Y'){
    Json::die([
        'result'=>false,
        'reason'=>'현재는 로그인이 금지되어있습니다!',
        'noRetry'=>true
    ]);
}

$nowDate = TimeUtil::DatetimeNow();

$access_token = Util::array_get($_SESSION['access_token']);
$expires = Util::array_get($_SESSION['expires']);
$refresh_token = Util::array_get($_SESSION['refresh_token']);
$refresh_token_expires = Util::array_get($_SESSION['refresh_token_expires']);
$email = Util::array_get($_SESSION['kaccount_email']);


if(!$access_token || !$expires){
    Json::die([
        'result'=>false,
        'reason'=>'카카오로그인이 이루어지지 않았습니다.'
    ]);
}

//TODO: join과 login의 동작이 비슷하다. helper class로 묶자.
$restAPI = new Kakao_REST_API_Helper($access_token);

if($expires < $nowDate && (!$refresh_token || ($refresh_token_expires < $nowDate))){
    Json::die([
        'result'=>false,
        'reason'=>'로그인 토큰 만료.'.$refresh_token_expires.' 다시 카카오로그인을 수행해주세요.'
    ]);
}

if($expires < $nowDate){
    unset($_SESSION['kaccount_email']);
    $email = null;

    $result = $restAPI->refresh_access_token($refresh_token);
    if(!isset($refresh_token)){
        Json::die([
            'result'=>false,
            'reason'=>'카카오 로그인 과정 중 추가 갱신 절차를 실패했습니다'
        ]);
    }

    $access_token = $result['access_token'];
    $expires = TimeUtil::DatetimeFromNowSecond($nowDate, $result['expires_in']);
    if(isset($result['refresh_token'])){
        $refresh_token = Util::array_get($result['refresh_token']);
        $refresh_token_expires = TimeUtil::DatetimeFromNowSecond($nowDate, $result['refresh_token_expires_in']);
    }
}


if(!$email){
    $me = $restAPI->meWithEmail();
    $me['code'] = Util::array_get($me['code'], 0);
    if ($me['code']< 0) {
        Json::die([
            'result'=>false,
            'reason'=>'카카오로그인이 정상적으로 수행되지 않았습니다.'
        ]);
    }

    if(!Util::array_get($me['kaccount_email_verified'],false)){
        $restAPI->unlink();
        Json::die([
            'result'=>false,
            'reason'=>'카카오 계정 이메일이 아직 인증되지 않았습니다'
        ]);
    }
    
    $email = $me['kaccount_email'];
    $_SESSION['kaccount_email'] = $email;
}


$userInfo = RootDB::db()->queryFirstRow(
    'SELECT `no`, `id`, `name`, `grade`, `delete_after` from member where email=%s',$email);

if(!$userInfo){
    $restAPI->unlink();
    unset($_SESSION['access_token']);
    Json::die([
        'result'=>false,
        'reason'=>'카카오로그인에 해당하는 계정이 없습니다. 재 가입을 시도해주세요.'
    ]);
}

if($userInfo['delete_after']){
    if($userInfo['delete_after'] < $nowDate){
        $restAPI->unlink();
        unset($_SESSION['access_token']);
        RootDB::db()->delete('member', 'no=%i', $userInfo['no']);
        Json::die([
            'result'=>false,
            'reason'=>"기간 만기로 삭제되었습니다. 재 가입을 시도해주세요."
        ]);
    }
    else{
        $restAPI->unlink();
        unset($_SESSION['access_token']);
        Json::die([
            'result'=>false,
            'reason'=>"삭제 요청된 계정입니다.[{$userInfo['delete_after']}]"
        ]);
    }
    
}

$session->login($userInfo['no'], $userInfo['id'], $userInfo['grade']);
Json::die([
    'result'=>true,
    'reason'=>'로그인 되었습니다.'
]);