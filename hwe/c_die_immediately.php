<?php
namespace sammo;

include "lib.php";
include "func.php";

$availableDieImmediately = false;

//로그인 검사
$session = Session::requireGameLogin();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$general = $db->queryFirstRow('SELECT no,name,owner_name,npc,lastrefresh FROM general WHERE owner=%i AND npc = 0', $userID);

if(!$general){
    header('location:b_myPage.php');
    die();
}

increaseRefresh("장수 삭제", 1);
$gameStor->cacheValues(['turnterm', 'opentime', 'turntime', 'year', 'month']);

if($gameStor->turntime <= $gameStor->opentime){
    //서버 가오픈시 할 수 있는 행동
    if(addTurn($general['lastrefresh'], $gameStor->turnterm, 2) <= TimeUtil::DatetimeNow()){
        $availableDieImmediately = true;
    }
}

if(!$availableDieImmediately){
    header('location:b_myPage.php');
    die();
}



$generalObj = General::createGeneralObjFromDB($general['no']);
if($generalObj instanceof DummyGeneral){
    trigger_error("올바르지 않은 삭제 프로세스 $userID", E_USER_WARNING);
}
$generalName = $generalObj->getName();
$josaYi = JosaUtil::pick($generalName, '이');
$generalObj->kill($db, true, "<Y>{$generalName}</>{$josaYi} 이 홀연히 모습을 <R>감추었습니다</>");

$session->logoutGame();
header('location:..');