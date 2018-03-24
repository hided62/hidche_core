<?php
namespace sammo;

require(__DIR__.'/../d_setting/conf_kakao.php');
require('_common.php');
require(ROOT.'/f_config/DB.php');

use \kakao\Kakao_REST_API_Helper as Kakao_REST_API_Helper;

$nowDate = TimeUtil::DatetimeNow();

$session = Session::Instance();
if(!$session->isLoggedIn()){
    Json::die([
        'result'=>false,
        'reason'=>'로그인이 되어있지 않습니다'
    ]);
}
$userID = $session->userID;
$access_token = Util::array_get($_SESSION['access_token']);
$expires = Util::array_get($_SESSION['expires']);
$refresh_token = Util::array_get($_SESSION['refresh_token']);
$refresh_token_expires = Util::array_get($_SESSION['refresh_token_expires']);

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

getRootDB()->query("lock tables member write, member_log write");

$isUser = getRootDB()->queryFirstRow(
    'SELECT count(`no`) from member where no=%i',$userID);
if(!$isUser){
    Json::die([
        'result'=>false,
        'reason'=>'회원이 아닙니다. 관리자에게 문의해주세요.'
    ]);
}

$newPassword = Util::randomStr(6);
$tmpPassword = Util::hashPassword(getGlobalSalt(), $newPassword);
$newSalt = bin2hex(random_bytes(8));
$newFinalPassword = Util::hashPassword($newSalt, $tmpPassword);

$sendResult = $restAPI->talk_to_me_default([
  "object_type"=> "text",
  "text"=> "임시 비밀번호는 $newPassword 입니다. 로그인 후 바로 다른 비밀번호로 변경해주세요.",
  "link"=> [
    "web_url"=> getServerBasepath(),
    "mobile_web_url" => getServerBasepath()
  ],
  "button_title"=> "로그인 페이지 열기"
]);
$sendResult['code'] = Util::array_get($sendResult['code'], 0);
if($sendResult['code'] < 0){
    Json::die([
        'result'=>false,
        'reason'=>'카카오톡 메시지를 보내지 못했습니다.'
    ]);
}

getRootDB()->update('member', [
    'pw'=>$newFinalPassword,
    'salt'=>$newSalt
],'no=%i', $userID);

getRootDB()->insert('member_log', [
    'member_no'=>$userID,
    'date'=>$nowDate,
    'action_type'=>'change_pw',
    'action'=>json_encode([
        'type'=>'kakao',
        'no'=>$userID,
        'token'=>$access_token
    ], JSON_UNESCAPED_UNICODE)
]);

getRootDB()->query("unlock tables");


Json::die([
    'result'=>true,
    'reason'=>'success'
]);