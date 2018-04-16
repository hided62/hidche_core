<?php
namespace sammo;

include 'lib.php';
include 'func.php';

//{msgID: 1206, response: true}
$session = Session::requireGameLogin([])->setReadOnly();


$generalID = Session::getInstance()->generalID;

if (!$generalID) {
    Json::die([
        'result'=>false,
        'reason'=>'로그인하지 않음'
    ]);
}

$jsonPost = WebUtil::parseJsonPost();

$msgID = Util::toInt($jsonPost['msgID']??null);
$msgResponse = $jsonPost['response']??null;

if ($msgID === null || !is_bool($msgResponse)) {
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 인자'
    ]);
}

$general = DB::db()->queryFirstRow('select `no`, `name`, `nation`, `nations`, `level`, `npc`, `gold`, `rice`, `troop` from `general` where `no` = %i', $generalID);
if(!$general){
    Json::die([
        'result'=>false,
        'reason'=>'존재하지 않는 장수'
    ]);
}

$msg = Message::getMessageByID($msgID);
if($msg === null){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 메시지'
    ]);
}

if($msgResponse){
    $result = $msg->agreeMessage($general['no']);
}
else{
    $result = $msg->declineMessage($general['no']);
}


Json::die([
    'result' => $result===DiplomaticMessage::ACCEPTED,
    'reason' => 'result'
]);