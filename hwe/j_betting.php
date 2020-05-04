<?php
namespace sammo;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();

$betTarget = Util::getPost('target', 'int', -1);
$betGold = Util::getPost('amount', 'int', 0);


if($betTarget < 0 || $betTarget >= 16 || $betGold < 10 || $betGold > 1000){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 입력입니다'
    ]);
}

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();
$generalID = $session->generalID;

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("베팅", 1);

$tournament = $gameStor->tournament;
if($tournament != 6) {
    Json::die([
        'result'=>false,
        'reason'=>'베팅 기간이 아닙니다.'
    ]);
}

$myGold = $db->queryFirstField('SELECT gold FROM general WHERE no = %i', $generalID);
$bets = $db->queryFirstList('SELECT * FROM betting WHERE general_id = %i', $generalID);
$bets = array_splice($bets, -16); 
$totalBet = array_sum($bets);
$oldBet = $bets[$betTarget];

$targetKey = "bet{$betTarget}";

if($myGold - $betGold < 500){
    Json::die([
        'result'=>false,
        'reason'=>'여유 자금이 부족합니다.'
    ]);
}

if($betGold + $totalBet > 1000){
    Json::die([
        'result'=>false,
        'reason'=>'베팅 허용 금액을 초과했습니다.'
    ]);
}

$db->update('betting', [
    $targetKey=>$db->sqleval('%b + %i', $targetKey, $betGold),
], 'general_id = %i', $generalID);
$db->update('general', [
    'gold'=>$db->sqleval('gold - %i', $betGold), 
], 'no = %i', $generalID);
$db->update('rank_data', [
    'value'=>$db->sqleval('value + %i', $betGold)
], 'general_id = %i AND type = "betgold"', $generalID);
$db->update('betting', [
    $targetKey=>$db->sqleval('%b + %i', $targetKey, $betGold)
], 'general_id = 0');

Json::die([
    'result'=>true,
    'reason'=>'SUCCESS'
]);