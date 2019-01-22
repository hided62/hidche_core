<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$isSecretBoard = Util::getReq('isSecret', 'bool', false);

increaseRefresh("외교부", 1);

$me = $db->queryFirstRow('SELECT no, nation, level, permission, con, turntime, belong, penalty FROM general WHERE owner=%i', $userID);


$con = checkLimit($me['con']);
if ($con >= 2) {
    Json::die([
        'result'=>false,
        'reason'=>'접속 제한입니다.'
    ]);
}

$permission = checkSecretPermission($me);
if($permission < 0){
    Json::die([
        'result'=>false,
        'reason'=>'국가에 소속되어있지 않습니다.'
    ]);
    
}

$letters = [];

foreach(
    $db->query(
        'SELECT * FROM ng_diplomacy WHERE src_nation_id = %i OR dest_nation_id = %i ORDER BY date desc',
        $me['nation'], $me['nation']
    ) as $letter
){

    if($permission < 3){
        $letter['detail'] = '(권한이 부족합니다)';
    }
    $letter['comment'] = [];
    $letter['aux'] = Json::decode($letter['aux']);
    $letters[$letter['no']] = $letter;
}

$nations = [];
foreach(getAllNationStaticInfo() as $nation){
    if($nation['nation'] == 0 || $nation['nation'] == $me['nation']){
        continue;
    }
    $nations[] = $nation;
}

Json::die([
    'result'=>true,
    'nations'=>$nations,
    'letters'=>$letters,
    'reason'=>'success'
]);

