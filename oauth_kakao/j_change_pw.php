<?php
namespace sammo;

require(__DIR__.'/../vendor/autoload.php');

use \kakao\Kakao_REST_API_Helper as Kakao_REST_API_Helper;

$now = TimeUtil::now();

$session = Session::requireLogin([
    'reason'=>'로그인이 되어있지 않습니다'
]);
$userID = Session::getUserID();
$access_token = $session->access_token;
$expires = $session->expires;

if(!$access_token || !$expires){
    Json::die([
        'result'=>false,
        'reason'=>'카카오로그인이 이루어지지 않았습니다.'
    ]);
}


//TODO: join과 login의 동작이 비슷하다. helper class로 묶자.
$restAPI = new Kakao_REST_API_Helper($access_token);

$session->logout();

if($expires < $now){
    Json::die([
        'result'=>false,
        'reason'=>'로그인 토큰 만료. 카카오 로그인을 먼저 수행해주세요.'
    ]);
}

RootDB::db()->query("lock tables member write, member_log write");

$oauthInfo = Json::decode(RootDB::db()->queryFirstField('SELECT oauth_info from member where no=%i',$userID))??[];
if(!$oauthInfo){
    Json::die([
        'result'=>false,
        'reason'=>'제대로 로그인이 이루어져 있지 않습니다.'
    ]);
}

$nextPasswordChange = $oauthInfo['nextPasswordChange']??null;
if($nextPasswordChange && $now < $nextPasswordChange){
    Json::die([
        'result'=>false,
        'reason'=>'비밀번호를 초기화한지 얼마 지나지 않았습니다.'
    ]);
}

$nextPasswordChange = TimeUtil::nowAddHours(4);
$oauthInfo['nextPasswordChange'] = $nextPasswordChange;


$newPassword = Util::randomStr(6);
$tmpPassword = Util::hashPassword(RootDB::getGlobalSalt(), $newPassword);
$newSalt = bin2hex(random_bytes(8));
$newFinalPassword = Util::hashPassword($newSalt, $tmpPassword);

$sendResult = $restAPI->talk_to_me_default([
  "object_type"=> "text",
  "text"=> "임시 비밀번호는 $newPassword 입니다. 로그인 후 바로 다른 비밀번호로 변경해주세요.",
  "link"=> [
    "web_url"=> ServConfig::getServerBasepath(),
    "mobile_web_url" => ServConfig::getServerBasepath()
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
    'salt'=>$newSalt,
    'oauth_info'=>Json::encode($oauthInfo),
],'no=%i', $userID);

RootDB::db()->insert('member_log', [
    'member_no'=>$userID,
    'date'=>$now,
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