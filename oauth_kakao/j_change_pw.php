<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

use \kakao\Kakao_REST_API_Helper as Kakao_REST_API_Helper;

$nowDate = TimeUtil::DatetimeNow();

$session = Session::requireLogin([
    'reason'=>'로그인이 되어있지 않습니다'
]);
$userID = Session::getUserID();
$access_token = $session->access_token;
$expires = $session->expires;
$refresh_token = $session->refresh_token;
$refresh_token_expires = $session->refresh_token_expires;

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
    $session->kaccount_email = null;
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

RootDB::db()->query("lock tables member write, member_log write");

$isUser = RootDB::db()->queryFirstRow(
    'SELECT count(`no`) from member where no=%i',$userID);
if(!$isUser){
    Json::die([
        'result'=>false,
        'reason'=>'회원이 아닙니다. 관리자에게 문의해주세요.'
    ]);
}

$newPassword = Util::randomStr(6);
$tmpPassword = Util::hashPassword(RootDB::getGlobalSalt(), $newPassword);
$newSalt = bin2hex(random_bytes(8));
$newFinalPassword = Util::hashPassword($newSalt, $tmpPassword);

$sendResult = $restAPI->talk_to_me_default([
  "object_type"=> "text",
  "text"=> "임시 비밀번호는 $newPassword 입니다. 로그인 후 바로 다른 비밀번호로 변경해주세요.",
  "link"=> [
    "web_url"=> RootDB::getServerBasepath(),
    "mobile_web_url" => RootDB::getServerBasepath()
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

RootDB::db()->update('member', [
    'pw'=>$newFinalPassword,
    'salt'=>$newSalt
],'no=%i', $userID);

RootDB::db()->insert('member_log', [
    'member_no'=>$userID,
    'date'=>$nowDate,
    'action_type'=>'change_pw',
    'action'=>Json::encode([
        'type'=>'kakao',
        'no'=>$userID,
        'token'=>$access_token
    ])
]);

RootDB::db()->query("unlock tables");


Json::die([
    'result'=>true,
    'reason'=>'success'
]);