<?php
namespace sammo;

include "lib.php";
include "func.php";
// $btn, $name, $troop
$btn = Util::getReq('btn');
$name = Util::getReq('name');
$troop = Util::getReq('troop', 'int');

extractMissingPostToGlobals();

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = $session::getUserID();

$db = DB::db();

$me = $db->queryFirstRow('SELECT `no`, nation, troop FROM general WHERE `owner`=%i', $userID);

$name = trim($name);
if($btn == "부 대 창 설" && $name != "" && $me['troop'] == 0) {
    $db->insert('troop',[
        'name'=>$name,
        'nation'=>$me['nation'],
        'no'=>$me['no']
    ]);
    $troopID = $db->insertId();

    $db->update('general', [
        'troop'=>$troopID
    ], 'no=%i',$me['no']);
} elseif($btn == "부 대 변 경" && $name != "") {
    $db->update('troop', [
        'name'=>$name
    ], 'no=%i',$me['no']);
} elseif($btn == "부 대 추 방" && $gen != 0) {
    $db->update('general', [
        'troop'=>0
    ], 'no=%i AND troop=(SELECT troop FROM troop WHERE no = %i)', $gen, $me['no']);
} elseif($btn == "부 대 가 입" && $troop != 0) {
    $troop = $db->queryFirstField('SELECT troop FROM troop WHERE no = %i', $troop);
    if($troop){
        $db->update('general', [
            'troop'=>$troop
        ], 'no=%i', $me['no']);
    }
} elseif($btn == "부 대 탈 퇴") {
    $troopLeader = $db->queryFirstField('SELECT `no` FROM troop WHERE troop=%i', $me['troop']);
    
    //부대장일 경우
    if($troopLeader == $me['no']) {
        // 모두 탈퇴
        $db->update('general', [
            'troop'=>0
        ], 'troop=%i',$me['troop']);
        // 부대 삭제
        $db->delete('troop', 'troop=%i', $me['troop']);
    } else {
        $db->update('general', [
            'troop'=>0
        ], 'no=%i', $me['no']);
    }
}

header('Location:b_troop.php');
