<?php
namespace sammo;

require_once('_common.php');
require('lib.join.php');
require('kakao.php');

use \kakao\Kakao_REST_API_Helper as Kakao_REST_API_Helper;

session_start();

$canJoin = RootDB::db()->queryFirstField('SELECT REG FROM `SYSTEM` WHERE `NO` = 1');
if($canJoin != 'Y'){
    Json::die([
        'result'=>false,
        'reason'=>'현재는 가입이 금지되어있습니다!'
    ]);
}

$nowDate = TimeUtil::DatetimeNow();

$access_token = Util::array_get($_SESSION['access_token']);
$expires = Util::array_get($_SESSION['expires']);
$refresh_token = Util::array_get($_SESSION['refresh_token']);
$refresh_token_expires = Util::array_get($_SESSION['refresh_token_expires']);
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
$secret_agree =Util::array_get($_POST['secret_agree']);
$username = mb_strtolower(Util::array_get($_POST['username']), 'utf-8');
$password = Util::array_get($_POST['password']);
$nickname = Util::array_get($_POST['nickname']);

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
$finalPassword = Util::hashPassword($userSalt, $password);

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
        $refresh_token = Util::array_get($result['refresh_token']);
        $refresh_token_expires = TimeUtil::DatetimeFromNowSecond($nowDate, $result['refresh_token_expires_in']);
    }
}


$signupResult = $restAPI->signup();
$kakaoID = Util::array_get($signupResult['id']);

if(!$kakaoID && Util::array_get($signupResult['msg'])!='already registered'){
    Json::die([
        'result'=>false,
        'reason'=>'카카오 로그인 과정 중 앱 연결 절차를 실패했습니다'.json_encode($signupResult)
    ]);
}

$me = $restAPI->meWithEmail();
$me['code'] = Util::array_get($me['code'], 0);
if ($me['code']< 0) {
    $restAPI->unlink();
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
$emailChk = checkEmailDup($email);
if($emailChk !== true){
    $restAPI->unlink();
    Json::die([
        'result'=>false,
        'reason'=>$emailChk
    ]);
}

//모든 절차 종료. 등록.
RootDB::db()->insert('member',[
    'oauth_id' => $kakaoID,
    'oauth_type' => 'KAKAO',
    'id' => $username,
    'email' => $email,
    'pw' => $finalPassword,
    'salt' => $userSalt,
    'name'=>$nickname,
    'reg_date'=>$nowDate
]);
$userID = RootDB::db()->insertId();

RootDB::db()->insert('member_log', [
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

