<?php
namespace sammo;

include('lib.php');
include('func.php');

$session = Session::requireGameLogin([]);
$userID = Session::getUserID();

$reqSequence = Util::getReq('sequence', 'int', -1);

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
    sleep(0.2);
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

$result = [];
$result['result'] = true;
$result['keepRecent'] = false;
$nextSequence = $reqSequence;
$minSequence = $reqSequence;
$lastType = null;

$result['private'] = array_map(function(Message $msg) use (&$nextSequence, &$minSequence, &$lastType){
    if($msg->id > $nextSequence){
        $nextSequence = $msg->id;
    }
    if($msg->id <= $minSequence){
        $minSequence = $msg->id;
        $lastType = 'private';
    }
    return $msg->toArray();
}, Message::getMessagesFromMailBox($generalID, Message::MSGTYPE_PRIVATE, 20, $reqSequence));

$result['public'] = array_map(function(Message $msg)use (&$nextSequence, &$minSequence, &$lastType){
    if($msg->id > $nextSequence){
        $nextSequence = $msg->id;
    }
    if($msg->id <= $minSequence){
        $minSequence = $msg->id;
        $lastType = 'public';
    }
    return $msg->toArray();
}, Message::getMessagesFromMailBox(Message::MAILBOX_PUBLIC, Message::MSGTYPE_PUBLIC, 20, $reqSequence));

$result['national'] = array_map(function(Message $msg)use (&$nextSequence, &$minSequence, &$lastType){
    if($msg->id > $nextSequence){
        $nextSequence = $msg->id;
    }
    if($msg->id <= $minSequence){
        $minSequence = $msg->id;
        $lastType = 'national';
    }
    return $msg->toArray();
}, Message::getMessagesFromMailBox(Message::MAILBOX_NATIONAL + $nationID, Message::MSGTYPE_NATIONAL, 40, $reqSequence));

$result['diplomacy']= array_map(function(Message $msg)use (&$nextSequence, &$minSequence, &$lastType){
    if($msg->id > $nextSequence){
        $nextSequence = $msg->id;
    }
    return $msg->toArray();
}, Message::getMessagesFromMailBox(Message::MAILBOX_NATIONAL + $nationID, Message::MSGTYPE_DIPLOMACY, 10, 0));

if($lastType !== null){
    array_pop($result[$lastType]);
    $result['keepRecent'] = true;
}
else if($reqSequence <= 0){
    $result['keepRecent'] = true;
}

$result['sequence'] = $nextSequence;
$result['nationID'] = $nationID;
$result['generalName'] = $generalName;
Json::die($result);