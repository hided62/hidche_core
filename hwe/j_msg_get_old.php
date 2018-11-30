<?php
namespace sammo;

include('lib.php');
include('func.php');

$session = Session::requireGameLogin([]);
$userID = Session::getUserID();

$reqTo = Util::getReq('to', 'int');
$reqType = Util::getReq('type', 'string');

if($reqTo === null){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 범위 입니다.'
    ]);
}
if($reqType === null || !in_array($reqType, ['private', 'public', 'national'])){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 타입입니다.'
    ]);
}

$lastMsgGet = Json::decode($session->lastMsgGet)??[];
$now = new \DateTime();
$delayTime = false;
if(count($lastMsgGet) >= 10){
    try{
        if($lastMsgGet[0] !== 'string'){
            throw new \Exception('Why not string?');
        }
        $first = new \DateTime($lastMsgGet[0]);
        $diff = $first->diff($now);
        if($diff->days == 0 && $diff->h > 0 && $diff->i == 0 && $diff->s <= 1){
            $delayTime = true;
        }
        array_shift($lastMsgGet);
    }
    catch(\Exception $e){
        $lastMsgGet = [];
    } 
}
$lastMsgGet[] = $now;
$session->lastMsgGet = Json::encode($lastMsgGet);

if($delayTime){
    sleep(1);
}
$session->setReadOnly();

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

$result = [
    'private'=>[],
    'public'=>[],
    'national'=>[],
    'diplomacy'=>[],
    'result'=>true,
    'keepRecent'=>true,
    'sequence'=>0,
    'nationID'=>$nationID,
    'generalName'=>$generalName,
];
$result['result'] = true;

$nextSequence = $reqTo;

if($reqType == 'private'){
    $result['private'] = array_map(function(Message $msg) use (&$nextSequence){
        if($msg->id > $nextSequence){
            $nextSequence = $msg->id;
        }
        return $msg->toArray();
    }, Message::getMessagesFromMailBoxOld($generalID, Message::MSGTYPE_PRIVATE, $reqTo, 20));
}
else if($reqType == 'public'){
    $result['public'] = array_map(function(Message $msg)use (&$nextSequence){
        if($msg->id > $nextSequence){
            $nextSequence = $msg->id;
        }
        return $msg->toArray();
    }, Message::getMessagesFromMailBoxOld(Message::MAILBOX_PUBLIC, Message::MSGTYPE_PUBLIC, $reqTo, 20));
}
else{
    $result['national'] = array_map(function(Message $msg)use (&$nextSequence){
        if($msg->id > $nextSequence){
            $nextSequence = $msg->id;
        }
        return $msg->toArray();
    }, Message::getMessagesFromMailBoxOld(Message::MAILBOX_NATIONAL + $nationID, Message::MSGTYPE_NATIONAL, $reqTo, 40));
}

Json::die($result);