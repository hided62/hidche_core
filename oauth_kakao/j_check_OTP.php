<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

$RootDB = RootDB::db();
$session = Session::getInstance();
if($session->isLoggedIn()){
    Json::die([
        'result'=>false,
        'reset'=>true,
        'reason'=>'이미 로그인 되어있습니다.'
    ]);
}

if(!$session->isLoggedIn(true)){
    Json::die([
        'result'=>false,
        'reset'=>true,
        'reason'=>'인증 코드를 입력할 수 있는 상태가 아닙니다.'
    ]);
}

$otp = Util::getReq('otp', 'int');
if(!$otp){
    Json::die([
        'result'=>false,
        'reset'=>false,
        'reason'=>'인증 코드가 입력되지 않았습니다.'
    ]);
}

$userNo = $session->getUserID();

$oauthInfo = Json::decode(RootDB::db()->queryFirstField('SELECT oauth_info FROM member WHERE no=%i', $userNo))??[];
if(!$oauthInfo){
    $session->logout();
    Json::die([
        'result'=>false,
        'reset'=>true,
        'reason'=>'계정이 정상적으로 등록되어있지 않습니다.'
    ]);
}


$OTPValue = $oauthInfo['OTPValue']??null;
$OTPTrialUntil = $oauthInfo['OTPTrialUntil']??null;
$OTPTrialCount = $oauthInfo['OTPTrialCount']??0;
$now = TimeUtil::DatetimeNow();

if(!$OTPTrialUntil || $OTPTrialUntil <= $now){
    $session->logout();
    Json::die([
        'result'=>false,
        'reset'=>true,
        'reason'=>'인증 기한이 만료되었습니다. 다시 로그인해주세요.'
    ]);
}

if($OTPTrialCount <= 0){
    Json::die([
        'result'=>false,
        'reset'=>false,
        'reason'=>"인증 실패 횟수를 초과했습니다. {$OTPTrialUntil}까지 기다려주세요."
    ]);
}

if($OTPValue != $otp){
    $OTPTrialCount -= 1;
    $oauthInfo['OTPTrialCount'] = $OTPTrialCount;
    RootDB::db()->update('member', [
        'oauth_info'=>Json::encode($oauthInfo)
    ], 'no=%i', $userNo);
    Json::die([
        'result'=>false,
        'reset'=>false,
        'reason'=>"인증 번호가 틀렸습니다. {$OTPTrialCount}회 더 시도할 수 있습니다."
    ]);
}

$OTPValidUntil = TimeUtil::DatetimeFromNowDay(10);
$session->setReqOTP(false);
$oauthInfo['OTPValidUntil'] = $OTPValidUntil;
RootDB::db()->update('member', [
    'oauth_info'=>Json::encode($oauthInfo)
], 'no=%i', $userNo);

Json::die([
    'result'=>true,
    'reset'=>false,
    'reason'=>"로그인을 성공했습니다. {$OTPValidUntil}까지 유효합니다."
]);