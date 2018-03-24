<?php
namespace sammo;

require(__DIR__.'/../d_setting/conf_kakao.php');
require_once('_common.php');
require('lib.join.php');
require('kakao.php');

use \kakao\Kakao_REST_API_Helper as Kakao_REST_API_Helper;

session_start();

$canJoin = getRootDB()->queryFirstField('SELECT REG FROM `SYSTEM` WHERE `NO` = 1');
if($canJoin != 'Y'){
    Json::die([
        'result'=>false,
        'reason'=>'현재는 가입이 금지되어있습니다!'
    ]);
}

$nowDate = TimeUtil::DatetimeNow();

$access_token = util::array_get($_SESSION['access_token']);
$expires = util::array_get($_SESSION['expires']);
$refresh_token = util::array_get($_SESSION['refresh_token']);
$refresh_token_expires = util::array_get($_SESSION['refresh_token_expires']);
if(!$access_token){
    Json::die([
        'result'=>false,
        'reason'=>'로그인 토큰 에러. 다시 카카오로그인을 수행해주세요.'
    ]);
}
if($expires < $nowDate && (!$refresh_token || ($refresh_token_expires < $nowDate))){
    unset($_SESSION['access_token']);
    Json::die([
        'result'=>false,
        'reason'=>'로그인 토큰 만료.'.$refresh_token_expires.' 다시 카카오로그인을 수행해주세요.'
    ]);
}
$secret_agree =util::array_get($_POST['secret_agree']);
$username = mb_strtolower(util::array_get($_POST['username']), 'utf-8');
$password = util::array_get($_POST['password']);
$nickname = util::array_get($_POST['nickname']);

if(!$username || !$password || !$nickname){
    Json::die([
        'result'=>false,
        'reason'=>'입력값이 설정되지 않았습니다.'
    ]);
}

if(strlen($password)!=128){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 비밀번호 해시 포맷입니다.'
    ]);
}

if(!$secret_agree){
    Json::die([
        'result'=>false,
        'reason'=>'약관에 동의해야 가입하실 수 있습니다.'
    ]);
}

$usernameChk = checkUsernameDup($username);
if($usernameChk !== true){
    Json::die([
        'result'=>false,
        'reason'=>$usernameChk
    ]);
}

$nicknameChk = checkNicknameDup($nickname);
if($nicknameChk !== true){
    Json::die([
        'result'=>false,
        'reason'=>$nicknameChk
    ]);
}

$userSalt = bin2hex(random_bytes(8));
$finalPassword = hashPassword($userSalt, $password);

//클라이언트 단에서 보내준 데이터 준비가 끝났다.
$restAPI = new Kakao_REST_API_Helper($access_token);

if($expires < $nowDate){
    $result = $restAPI->refresh_access_token($refresh_token);
    if(!isset($refresh_token)){
        unset($_SESSION['access_token']);
        Json::die([
            'result'=>false,
            'reason'=>'카카오 로그인 과정 중 추가 갱신 절차를 실패했습니다'
        ]);
    }

    $access_token = $result['access_token'];
    $expires = TimeUtil::DatetimeFromNowSecond($nowDate, $result['expires_in']);
    if(isset($result['refresh_token'])){
        $refresh_token = util::array_get($result['refresh_token']);
        $refresh_token_expires = TimeUtil::DatetimeFromNowSecond($nowDate, $result['refresh_token_expires_in']);
    }
}


$signupResult = $restAPI->signup();
$kakaoID = util::array_get($signupResult['id']);

if(!$kakaoID && util::array_get($signupResult['msg'])!='already registered'){
    Json::die([
        'result'=>false,
        'reason'=>'카카오 로그인 과정 중 앱 연결 절차를 실패했습니다'.json_encode($signupResult)
    ]);
}

$me = $restAPI->meWithEmail();
$me['code'] = util::array_get($me['code'], 0);
if ($me['code']< 0) {
    $restAPI->unlink();
    Json::die([
        'result'=>false,
        'reason'=>'카카오로그인이 정상적으로 수행되지 않았습니다.'
    ]);
}

if(!util::array_get($me['kaccount_email_verified'],false)){
    $restAPI->unlink();
    Json::die([
        'result'=>false,
        'reason'=>'카카오 계정 이메일이 아직 인증되지 않았습니다'
    ]);
}

$email = $me['kaccount_email'];
$_SESSION['kaccount_email'] = $email;
$emailChk = checkEmailDup($email);
if($emailChk !== true){
    $restAPI->unlink();
    Json::die([
        'result'=>false,
        'reason'=>$emailChk
    ]);
}

//모든 절차 종료. 등록.
getRootDB()->insert('member',[
    'oauth_id' => $kakaoID,
    'oauth_type' => 'KAKAO',
    'id' => $username,
    'email' => $email,
    'pw' => $finalPassword,
    'salt' => $userSalt,
    'name'=>$nickname,
    'reg_date'=>$nowDate
]);
$userID = getRootDB()->insertId();

getRootDB()->insert('member_log', [
    'member_no'=>$userID,
    'date'=>$nowDate,
    'action_type'=>'reg',
    'action'=>json_encode([
        'type'=>'kakao',
        'id'=>$kakaoID,
        'email'=>$email, 'name'=>$nickname
    ], JSON_UNESCAPED_UNICODE)
]);

Json::die([
    'result'=>true,
    'reason'=>'success'
]);

