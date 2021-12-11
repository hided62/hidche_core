<?php
namespace sammo;

include 'lib.php';
include 'func.php';

WebUtil::requireAJAX();

//{msgID: 1206, response: true}
$session = Session::requireGameLogin([])->setReadOnly();


$generalID = Session::getInstance()->generalID;

if (!$generalID) {
    Json::die([
        'result'=>false,
        'reason'=>'로그인하지 않음'
    ]);
}

$jsonPost = Json::decode(Util::getPost('data', 'string', '{}'));

$msgID = Util::toInt($jsonPost['msgID']??null);
$msgResponse = $jsonPost['response']??null;

if ($msgID === null || !is_bool($msgResponse)) {
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 인자'
    ]);
}

$general = DB::db()->queryFirstRow('SELECT `no`, `name`, `nation`, `officer_level`, `npc`, `gold`, `rice`, `troop`, `aux` from `general` where `no` = %i', $generalID);
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
$reason = 'success';
$gameStor = KVStorage::getStorage(DB::db(), 'game_env');
$gameStor->cacheAll();
if($msgResponse){
    $result = $msg->agreeMessage($general['no'], $reason);
}
else{
    $result = $msg->declineMessage($general['no'], $reason);
}


Json::die([
    'result' => $result!==DiplomaticMessage::INVALID,
    'reason' => $reason
]);