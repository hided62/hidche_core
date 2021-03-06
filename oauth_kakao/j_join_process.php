<?php
namespace sammo;

require(__DIR__.'/../vendor/autoload.php');
require('lib.join.php');

WebUtil::requireAJAX();

use \kakao\Kakao_REST_API_Helper as Kakao_REST_API_Helper;

$session = Session::getInstance();

$canJoin = RootDB::db()->queryFirstField('SELECT REG FROM `system` WHERE `NO` = 1');
if($canJoin != 'Y'){
    Json::die([
        'result'=>false,
        'reason'=>'현재는 가입이 금지되어있습니다!'
    ]);
}

$nowDate = TimeUtil::now();

$access_token = $session->access_token;
$expires = $session->expires;
$refresh_token = $session->refresh_token;
$refresh_token_expires = $session->refresh_token_expires;
if(!$access_token){
    Json::die([
        'result'=>false,
        'reason'=>'로그인 토큰 에러. 다시 카카오로그인을 수행해주세요.'
    ]);
}
if($expires < $nowDate && (!$refresh_token || ($refresh_token_expires < $nowDate))){
    $session->access_token = null;
    Json::die([
        'result'=>false,
        'reason'=>'로그인 토큰 만료.'.$refresh_token_expires.' 다시 카카오로그인을 수행해주세요.'
    ]);
}
$secret_agree = Util::getPost('secret_agree', 'bool');
$secret_agree2 = Util::getPost('secret_agree2', 'bool');
$third_use = Util::getPost('third_use', 'bool');
$username = mb_strtolower(Util::getPost('username'), 'utf-8');
$password = Util::getPost('password');
$nickname = Util::getPost('nickname');

if(!$username || !$password || !$nickname){
    Json::die([
        'result'=>false,
        'reason'=>'입력값이 설정되지 않았습니다.'
    ]);
}

'@phan-var-force string $password';

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

if(!$secret_agree2){
    Json::die([
        'result'=>false,
        'reason'=>'개인정보 제공 및 이용에 대해 동의해야 가입하실 수 있습니다.'
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
        $session->access_token = null;
        Json::die([
            'result'=>false,
            'reason'=>'카카오 로그인 과정 중 추가 갱신 절차를 실패했습니다'
        ]);
    }

    $access_token = $result['access_token'];
    $expires = TimeUtil::nowAddSeconds($result['expires_in']);
    if(isset($result['refresh_token'])){
        $refresh_token = Util::array_get($result['refresh_token']);
        $refresh_token_expires = TimeUtil::nowAddSeconds($result['refresh_token_expires_in']);
    }
}


$signupResult = $restAPI->signup();
$kakaoID = Util::array_get($signupResult['id']);

if(!$kakaoID && Util::array_get($signupResult['msg'])!='already registered'){
    Json::die([
        'result'=>false,
        'reason'=>'카카오 로그인 과정 중 앱 연결 절차를 실패했습니다'.Json::encode($signupResult)
    ]);
}

$me = $restAPI->meWithEmail();
$me['code'] = Util::array_get($me['code'], 0);
$kakao_account = $me['kakao_account']??[];
if ($me['code']< 0) {
    $restAPI->unlink();
    Json::die([
        'result'=>false,
        'reason'=>'카카오로그인이 정상적으로 수행되지 않았습니다.',
    ]);
}

if(!($kakao_account['has_email']??false)){
    Json::die([
        'result'=>false,
        'reqOTP'=>false,
        'reason'=>'이메일 정보 공유를 허가해 주셔야 합니다.',
    ]);
}

$validEmail = $kakao_account['is_email_valid']??false;
$verifiedEmail = $kakao_account['is_email_verified']??false;

if(!$validEmail || !$verifiedEmail){
    $restAPI->unlink();
    Json::die([
        'result'=>false,
        'reason'=>'카카오 계정 이메일이 아직 인증되지 않았습니다',
    ]);
}

$email = $kakao_account['email']??null;
$session->kaccount_email = $email;
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
    'third_use'=> $third_use,
    'pw' => $finalPassword,
    'salt' => $userSalt,
    'name'=>$nickname,
    'reg_date'=>$nowDate,
    'oauth_info'=>Json::encode([
        'accessToken'=>$access_token,
        'refreshToken'=>$refresh_token,
        'accessTokenValidUntil'=>$expires,
        'refreshTokenValidUntil'=>$refresh_token_expires
    ])
]);
$userID = RootDB::db()->insertId();

RootDB::db()->insert('member_log', [
    'member_no'=>$userID,
    'date'=>$nowDate,
    'action_type'=>'reg',
    'action'=>Json::encode([
        'type'=>'kakao',
        'id'=>$kakaoID,
        'email'=>$email, 'name'=>$nickname
    ])
]);

Json::die([
    'result'=>true,
    'reason'=>'success'
]);

