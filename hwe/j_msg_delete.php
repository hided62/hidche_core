<?php
namespace sammo;

include('lib.php');
include('func.php');

$session = Session::requireGameLogin([]);
$userID = Session::getUserID();

$msgID = Util::getReq('msgID', 'int');
if($msgID === null){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 범위 입니다.'
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
        if($diff->days == 0 && $diff->h > 0 && $diff->i == 0 && $diff->s <= 5){
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

$reason = Message::deleteMsg($msgID, $generalID);
if($reason === null){
    $result = [
        'result'=>true,
        'reason'=>'success'
    ];
}
else{
    $result = [
        'result'=>false,
        'reason'=>$reason
    ];
}


Json::die($result);