<?php
namespace sammo;

include "lib.php";
include "func.php";
// $btn, $name, $troop
$btn = Util::getReq('btn');
$name = Util::getReq('name');
$gen = Util::getReq('gen', 'int');
$troop = Util::getReq('troop', 'int');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = $session::getUserID();

$db = DB::db();

$me = $db->queryFirstRow('SELECT `no`, nation, troop FROM general WHERE `owner`=%i', $userID);


if($name && mb_strwidth($name) > 18){
    $name = mb_strimwidth($name, 0, 18);
}

$name = trim($name);
if($btn == "부 대 창 설" && $name != "" && $me['troop'] == 0) {
    $db->insertIgnore('troop',[
        'troop_leader'=>$me['no'],
        'name'=>$name,
        'nation'=>$me['nation'],
    ]);

    $db->update('general', [
        'troop'=>$me['no'],
    ], 'no=%i',$me['no']);
} elseif($btn == "부 대 변 경" && $name != "") {
    $db->update('troop', [
        'name'=>$name
    ], 'troop_leader=%i',$me['no']);
} elseif($btn == "부 대 추 방" && $gen != 0) {
    $db->update('general', [
        'troop'=>0
    ], 'no=%i AND troop= %i', $gen, $me['no']);
} elseif($btn == "부 대 가 입" && $troop != 0) {
    $troopLeaderNation = $db->queryFirstField('SELECT `nation` FROM `general` WHERE `no`=%i AND `troop`=%i AND `nation`=%i', $troop, $troop, $me['nation']);
    if($troopLeaderNation){
        $db->update('general', [
            'troop'=>$troop
        ], 'no=%i', $me['no']);
    }
} elseif($btn == "부 대 탈 퇴") {
    //부대장일 경우
    if($me['troop'] == $me['no']) {
        // 모두 탈퇴
        $db->update('general', [
            'troop'=>0
        ], 'troop=%i',$me['troop']);
        // 부대 삭제
        $db->delete('troop', 'troop_leader=%i', $me['troop']);
    } else {
        $db->update('general', [
            'troop'=>0
        ], 'no=%i', $me['no']);
    }
}

header('Location:b_troop.php', true, 303);
