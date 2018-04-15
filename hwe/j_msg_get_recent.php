<?php
namespace sammo;

include('lib.php');
include('func.php');

$session = Session::requireGameLogin([])->setReadOnly();
$userID = Session::getUserID();

$jsonPost = WebUtil::parseJsonPost();
$reqSequence = Util::toInt(Util::array_get($jsonPost['sequence'], 0));


list($generalID, $nationID) = DB::db()->queryFirstList(
    'select `no`, `nation` from `general` where owner = %i',
    $userID
);


if($nationID === null){
    Json::die([
        'result'=>false,
        'reason'=>'소속 국가가 없습니다'
    ]);
}

Json::die([
    'result'=>true,
    'private'=>array_map(function(Message $msg){
        return $msg->toArray();
    }, Message::getMessagesFromMailBox($generalID, Message::MSGTYPE_PRIVATE, 10, $reqSequence)),
    'public'=>array_map(function(Message $msg){
        return $msg->toArray();
    }, Message::getMessagesFromMailBox(Message::MAILBOX_PUBLIC, Message::MSGTYPE_PUBLIC, 20, $reqSequence)),
    'national'=>array_map(function(Message $msg){
        return $msg->toArray();
    }, Message::getMessagesFromMailBox(Message::MAILBOX_NATIONAL + $nationID, Message::MSGTYPE_NATIONAL, 20, $reqSequence)),
    'diplomacy'=>array_map(function(Message $msg){
        return $msg->toArray();
    }, Message::getMessagesFromMailBox(Message::MAILBOX_NATIONAL + $nationID, Message::MSGTYPE_DIPLOMACY, 10, 0)),
]);