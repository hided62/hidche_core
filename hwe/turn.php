<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사

$type = Util::getReq('type', 'int', 0);
$sel = Util::getReq('sel', 'int', 1);

if($sel <= 0 || $sel > 12){
    $sel = 1;
}

$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$generalID = $session->generalID;
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("턴반복", 1);

$myActionCnt = $db->queryFirstField('SELECT con FROM general WHERE `owner`=%i', $userID);

$con = checkLimit($myActionCnt);
if($con >= 2) { 
    header('location:commandlist.php');
    exit();
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
case 1://미루기
    pushGeneralCommand($generalID, $sel);
    break;
case 2://당기기
    pullGeneralCommand($generalID, $sel);
    break;
}

header('location:commandlist.php');
