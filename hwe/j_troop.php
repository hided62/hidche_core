<?php
namespace sammo;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();
// $btn, $name, $troop
$action = Util::getPost('action');
$name = Util::getPost('name');
$gen = Util::getPost('gen', 'int');
$troop = Util::getPost('troop', 'int');

//로그인 검사
$session = Session::requireGameLogin([])->setReadOnly();
$userID = $session::getUserID();

increaseRefresh("부대명령", 0);

$db = DB::db();

$me = $db->queryFirstRow('SELECT `no`, name, nation, troop FROM general WHERE `owner`=%i', $userID);
$generalID = $me['no'];
$nationID = $me['nation'];

if($action == '부대창설'){
    $name = StringUtil::neutralize($name);
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
        'troop_leader'=>$generalID,
        'name'=>$name,
        'nation'=>$nationID,
    ]);

    $db->update('general', [
        'troop'=>$generalID,
    ], 'no=%i',$generalID);

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
    ], 'no=%i AND troop = %i', $gen, $generalID);

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

    if($me['troop'] === $generalID){
        Json::die([
            'result'=>false,
            'reason'=>'부대장입니다.'
        ]);
    }

    $troopExists = $db->queryFirstField('SELECT `troop_leader` FROM `troop` WHERE `troop_leader` = %i AND `nation` = %i', $troop, $nationID);

    if (!$troopExists) {
        Json::die([
            'result'=>false,
            'reason'=>'올바른 부대장이 아닙니다.'
        ]);
    }

    $db->update('general', [
        'troop'=>$troop
    ], 'no=%i', $generalID);

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

    //부대장일 경우
    if($me['troop'] === $generalID){
        $db->update('general', [
            'troop'=>0
        ], 'troop=%i',$generalID);
        $db->delete('troop', 'troop_leader=%i', $generalID);
    }
    else{
        $db->update('general', [
            'troop'=>0
        ], 'no=%i', $generalID);
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