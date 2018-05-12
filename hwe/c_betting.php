<?php
namespace sammo;

include "lib.php";
include "func.php";


// $btn0~15, $gold0~15

//FIXME: 으악!!!!! 왜?! idx를 지정하고 gold를 지정하라고!
$betTarget = -1;
$betGold = -1;
for($i=0;$i<16;$i++){
    $textBtn = "btn{$i}";
    $textGold = "gold{$i}";
    $btn = Util::getReq($textBtn);
    $gold = Util::getReq($textGold, 'int');
    if($btn === "베팅!" && $gold){
        $betTarget = $i;
        $betGold = $gold;
        break;
    }
}

if($betGold == -1){
    extractMissingPostToGlobals();
}


if($betTarget < 0 || $betGold < 10 || $betGold > 1000){
    header('Location: b_betting.php');
    exit();
}


//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("베팅", 1);

$tournament = $db->queryFirstField('SELECT tournament FROM game LIMIT 1');
if($tournament != 6) {
    header('Location: b_betting.php');
    exit();
}

$bets = $db->queryFirstList('SELECT bet0,bet1,bet2,bet3,bet4,bet5,bet6,bet7,bet8,bet9,bet10,bet11,bet12,bet13,bet14,bet15,gold FROM general WHERE `owner`=%i', $userID);
$myGold = array_pop($bets);
$totalBet = array_sum($bets);

//NOTE: 위 코드에서 $betTarget은 0~15의 정수임이 보장된다.
$oldBet = $bets[$betTarget];
if($betGold + 500 <= $myGold && $betGold + $oldBet <= 1000 && $betGold + $totalBet <= 1000) {
    $db->update('general', [
        'gold'=>$db->sqleval('gold - %i', $betGold),
        "bet{$betTarget}"=>$db->sqleval("bet{$betTarget} + %i", $betGold),
        'betgold'=>$db->sqleval('betgold + %i', $betGold)
    ], 'owner = %i', $userID);

    $gameStor->setValue("bet{$betTarget}", $gameStor->getValue("bet{$betTarget}") + $betGold);//TODO: +로 증가하는 storage값은 별도로 분리
}

header('location: b_betting.php');
