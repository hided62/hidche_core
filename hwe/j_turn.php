<?php
namespace sammo;

include "lib.php";
include "func.php";



$session = Session::requireGameLogin([])->setReadOnly();

$type = Util::getReq('type', 'int', 0);
$sel = Util::getReq('sel', 'int', 1);

if($sel <= 0 || $sel > 12){
    $sel = 1;
}

$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("턴반복", 1);

$myActionCnt = $db->queryFirstField('SELECT con FROM general WHERE `owner`=%i', $userID);

$con = checkLimit($myActionCnt);
if($con >= 2) { 
    Json::die([
        'result'=>false,
        'result'=>'접속 제한입니다.'
    ]);
 }

switch($type) {
case 0://반복
    $valueMap = [];
    foreach(range($sel, GameConst::$maxTurn - 1) as $idx){
        $src = $idx % $sel;
        $valueMap['turn'.$idx] = $db->sqleval('%b', 'turn'.$src);
    }
    $db->update('general', $valueMap, 'owner=%i', $userID);
    break;
case 1:
    $valueMap = [];
    foreach(range(GameConst::$maxTurn -1, $sel, -1) as $idx){
        $src = $idx - $sel;
        $valueMap['turn'.$idx] = $db->sqleval('%b', 'turn'.$src);
    }
    foreach(range($sel -1, 0, -1) as $idx){
        $valueMap['turn'.$idx] = EncodeCommand(0, 0, 0, 0);
    }
    $db->update('general', $valueMap, 'owner=%i', $userID);
    break;
case 2:
    $valueMap = [];
    foreach(range(0, GameConst::$maxTurn - $sel - 1) as $idx){
        $src = $idx + $sel;
        $valueMap['turn'.$idx] = $db->sqleval('%b', 'turn'.$src);
    }
    foreach(range(GameConst::$maxTurn - $sel, GameConst::$maxTurn - 1) as $idx){
        $valueMap['turn'.$idx] = EncodeCommand(0, 0, 0, 0);
    }
    $db->update('general', $valueMap, 'owner=%i', $userID);
    break;
}

Json::die([
    'result'=>true,
    'result'=>'success'
]);