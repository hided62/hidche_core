<?php
namespace sammo;

include "lib.php";
include "func.php";
// $btn, $name, $troop
$action = Util::getReq('action');
$name = Util::getReq('name');
$gen = Util::getReq('gen', 'int');
$troop = Util::getReq('troop', 'int');

//로그인 검사
$session = Session::requireGameLogin([])->setReadOnly();
$userID = $session::getUserID();

$db = DB::db();

$me = $db->queryFirstRow('SELECT `no`, name, nation, troop FROM general WHERE `owner`=%i', $userID);



if($action == '부대창설'){
    $name = trim($name);
    if(!$name){
        Json::die([
            'result'=>false,
            'reason'=>'부대 이름이 없습니다.'
        ]);
    }
    if($me['troop'] != 0){
        Json::die([
            'result'=>false,
            'reason'=>'이미 부대에 가입해있습니다.'
        ]);
    }
    $db->insert('troop',[
        'name'=>$name,
        'nation'=>$me['nation'],
        'no'=>$me['no']
    ]);
    $troopID = $db->insertId();

    $db->update('general', [
        'troop'=>$troopID
    ], 'no=%i',$me['no']);

    Json::die([
        'result'=>true,
        'reason'=>'success'
    ]);
}

if($action == '부대변경'){
    $name = trim($name);
    if(!$name){
        Json::die([
            'result'=>false,
            'reason'=>'부대 이름이 없습니다.'
        ]);
    }

    $db->update('troop', [
        'name'=>$name
    ], 'no=%i',$me['no']);

    if($db->affectedRows() == 0){
        Json::die([
            'result'=>false,
            'reason'=>'부대장이 아닙니다.'
        ]);
    }

    Json::die([
        'result'=>true,
        'reason'=>'success'
    ]);
}

if($action == '부대추방'){
    if (!$gen){
        Json::die([
            'result'=>false,
            'reason'=>'장수를 지정해야 합니다.'
        ]);
    }

    $db->update('general', [
        'troop'=>0
    ], 'no=%i AND troop=(SELECT troop FROM troop WHERE no = %i)', $gen, $me['no']);

    if($db->affectedRows() == 0){
        Json::die([
            'result'=>false,
            'reason'=>'부대장이 아니거나, 장수를 잘못 지정했습니다.'
        ]);
    }

    Json::die([
        'result'=>true,
        'reason'=>'success'
    ]);
}

if ($action == '부대가입') {
    if(!$troop){
        Json::die([
            'result'=>false,
            'reason'=>'부대를 지정해야 합니다.'
        ]);
    }

    $troopLeader = $db->queryFirstField('SELECT `no` FROM troop WHERE troop=%i', $troop)?:0;
    $troopLeaderNation = $db->queryFirstField('SELECT `nation` FROM `general` WHERE `no`=%i AND `nation`=%i', $troopLeader, $me['nation']);

    if (!$troopLeaderNation) {
        Json::die([
            'result'=>false,
            'reason'=>'올바른 부대장이 아닙니다.'
        ]);
    }

    $db->update('general', [
        'troop'=>$troop
    ], 'no=%i', $me['no']);

    Json::die([
        'result'=>true,
        'reason'=>'success'
    ]);
}

if($action ==  "부대탈퇴") {

    if($me['troop'] == 0){
        Json::die([
            'result'=>false,
            'reason'=>'부대에 속해있지 않습니다.'
        ]);
    }
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

    Json::die([
        'result'=>true,
        'reason'=>'success'
    ]);
}

Json::die([
    'result'=>false,
    'reason'=>'올바르지 않은 명령입니다.'
]);