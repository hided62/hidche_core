<?php
namespace sammo;

use sammo\Enums\MessageType;

include('lib.php');
include('func.php');

$session = Session::requireGameLogin([]);
$userID = Session::getUserID();

$reqSequence = Util::getPost('sequence', 'int', -1);

$lastMsgGet = Json::decode($session->lastMsgGet)??[];
$now = new \DateTime();
$delayTime = false;
if(count($lastMsgGet) >= 10){
    try{
        if(!is_string($lastMsgGet[0])){
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
$lastMsgGet[] = TimeUtil::now();
$session->lastMsgGet = Json::encode($lastMsgGet);

if($delayTime){
    usleep(200);
}
$session->setReadOnly();


$db = DB::db();
$me = $db->queryFirstRow('SELECT `no`,`name`,`nation`,`officer_level`,`con`,`picture`,`imgsvr`,penalty,permission FROM general WHERE `owner`=%i', $userID);

if($me === null){
    Json::die([
        'result'=>false,
        'reason'=>'장수가 사망했습니다.'
    ]);
}

[$generalID, $nationID, $generalName] = [$me['no'], $me['nation'], $me['name']];
$permission = checkSecretPermission($me, false);

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
}, Message::getMessagesFromMailBox($generalID, MessageType::private, 15, $reqSequence));

$result['public'] = array_map(function(Message $msg)use (&$nextSequence, &$minSequence, &$lastType){
    if($msg->id > $nextSequence){
        $nextSequence = $msg->id;
    }
    if($msg->id <= $minSequence){
        $minSequence = $msg->id;
        $lastType = 'public';
    }
    return $msg->toArray();
}, Message::getMessagesFromMailBox(Message::MAILBOX_PUBLIC, MessageType::public, 15, $reqSequence));

$result['national'] = array_map(function(Message $msg)use (&$nextSequence, &$minSequence, &$lastType){
    if($msg->id > $nextSequence){
        $nextSequence = $msg->id;
    }
    if($msg->id <= $minSequence){
        $minSequence = $msg->id;
        $lastType = 'national';
    }
    return $msg->toArray();
}, Message::getMessagesFromMailBox(Message::MAILBOX_NATIONAL + $nationID, MessageType::national, 15, $reqSequence));

$result['diplomacy']= array_map(function(Message $msg)use (&$nextSequence, &$minSequence, &$lastType, $permission){
    if($msg->id > $nextSequence){
        $nextSequence = $msg->id;
    }
    if($msg->id <= $minSequence){
        $minSequence = $msg->id;
        $lastType = 'diplomacy';
    }
    $values = $msg->toArray();
    if($msg->dest->nationID != 0 && $permission < 3){
        $values['text'] = '(외교 메시지입니다)';//TODO: 외교서신이라 읽을 수 없음을 보여줘야함
        $values['option']['invalid'] = true;
    }
    return $values;
}, Message::getMessagesFromMailBox(Message::MAILBOX_NATIONAL + $nationID, MessageType::diplomacy, 15, $reqSequence));

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