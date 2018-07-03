<?php
namespace sammo;

include('lib.php');
include('func.php');

$session = Session::requireGameLogin([])->setReadOnly();
$userID = Session::getUserID();

$reqSequence = Util::getReq('sequence', 'int', 0);


list($generalID, $nationID, $generalName) = DB::db()->queryFirstList(
    'select `no`, `nation`, `name` from `general` where owner = %i',
    $userID
);


if($nationID === null){
    Json::die([
        'result'=>false,
        'reason'=>'장수가 사망했습니다.'
    ]);
}

$result = [];
$result['result'] = true;

$nextSequence = $reqSequence;

$result['private'] = array_map(function(Message $msg) use (&$nextSequence){
    if($msg->id > $nextSequence){
        $nextSequence = $msg->id;
    }
    return $msg->toArray();
}, Message::getMessagesFromMailBox($generalID, Message::MSGTYPE_PRIVATE, 10, $reqSequence));

$result['public'] = array_map(function(Message $msg)use (&$nextSequence){
    if($msg->id > $nextSequence){
        $nextSequence = $msg->id;
    }
    return $msg->toArray();
}, Message::getMessagesFromMailBox(Message::MAILBOX_PUBLIC, Message::MSGTYPE_PUBLIC, 10, $reqSequence));

$result['national'] = array_map(function(Message $msg)use (&$nextSequence){
    if($msg->id > $nextSequence){
        $nextSequence = $msg->id;
    }
    return $msg->toArray();
}, Message::getMessagesFromMailBox(Message::MAILBOX_NATIONAL + $nationID, Message::MSGTYPE_NATIONAL, 20, $reqSequence));

$result['diplomacy']= array_map(function(Message $msg)use (&$nextSequence){
    if($msg->id > $nextSequence){
        $nextSequence = $msg->id;
    }
    return $msg->toArray();
}, Message::getMessagesFromMailBox(Message::MAILBOX_NATIONAL + $nationID, Message::MSGTYPE_DIPLOMACY, 10, 0));

$result['sequence'] = $nextSequence;
$result['nationID'] = $nationID;
$result['generalName'] = $generalName;
Json::die($result);