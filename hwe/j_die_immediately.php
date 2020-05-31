<?php
namespace sammo;

include "lib.php";
include "func.php";


WebUtil::requireAJAX();

$availableDieImmediately = false;

//로그인 검사
$session = Session::requireGameLogin();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$general = $db->queryFirstRow('SELECT no,name,owner_name,npc,lastrefresh FROM general WHERE owner=%i AND npc = 0', $userID);

if(!$general){
    Json::die([
        'result'=>false,
        'reason'=>'장수가 없습니다.'
    ]);
}

increaseRefresh("장수 삭제", 1);
$gameStor->cacheValues(['turnterm', 'opentime', 'turntime', 'year', 'month']);

if($gameStor->turntime > $gameStor->opentime){
    Json::die([
        'result'=>false,
        'reason'=>'이미 서버가 시작되었습니다.'
    ]);
}


$targetTime = addTurn($general['lastrefresh'], $gameStor->turnterm, 2);
if($targetTime > TimeUtil::now()){
    $targetTimeShort = substr($targetTime, 0, 19);
    Json::die([
        'result'=>false,
        'reason'=>"아직 삭제할 수 없습니다. {$targetTimeShort} 부터 가능합니다."
    ]);
}


$generalObj = General::createGeneralObjFromDB($general['no']);
if($generalObj instanceof DummyGeneral){
    trigger_error("올바르지 않은 삭제 프로세스 $userID", E_USER_WARNING);
}
$generalName = $generalObj->getName();
$josaYi = JosaUtil::pick($generalName, '이');
$generalObj->kill($db, true, "<Y>{$generalName}</>{$josaYi} 이 홀연히 모습을 <R>감추었습니다</>");

$session->logoutGame();
Json::die([
    'result'=>true,
    'reason'=>'success'
]);