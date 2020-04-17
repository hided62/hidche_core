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
if($reqType === null || !in_array($reqType, ['private', 'public', 'national', 'diplomacy'])){
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

$me = DB::db()->queryFirstRow('SELECT `no`,`name`,`nation`,`officer_level`,`con`,`picture`,`imgsvr`,penalty,permission FROM general WHERE `owner`=%i', $userID);

if($me === null){
    Json::die([
        'result'=>false,
        'reason'=>'장수가 사망했습니다.'
    ]);
}

[$generalID, $nationID, $generalName] = [$me['no'], $me['nation'], $me['name']];
$permission = checkSecretPermission($me, false);

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
else if($reqType == 'national'){
    $result['national'] = array_map(function(Message $msg)use (&$nextSequence){
        if($msg->id > $nextSequence){
            $nextSequence = $msg->id;
        }
        return $msg->toArray();
    }, Message::getMessagesFromMailBoxOld(Message::MAILBOX_NATIONAL + $nationID, Message::MSGTYPE_NATIONAL, $reqTo, 20));
}
else{
    $result['diplomacy'] = array_map(function(Message $msg)use (&$nextSequence, $permission){
        if($msg->id > $nextSequence){
            $nextSequence = $msg->id;
        }
        $values = $msg->toArray();
        if($msg->dest->nationID != 0 && $permission < 3){
            $values['text'] = '(외교 메시지입니다)';//TODO: 외교서신이라 읽을 수 없음을 보여줘야함
            $values['option']['invalid'] = true;
        }
        return $values;
    }, Message::getMessagesFromMailBoxOld(Message::MAILBOX_NATIONAL + $nationID, Message::MSGTYPE_DIPLOMACY, $reqTo, 20));
}

Json::die($result);