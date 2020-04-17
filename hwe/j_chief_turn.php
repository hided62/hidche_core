<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$turnAmount = Util::getReq('amount', 'int');

$db = DB::db();
$me = $db->queryFirstRow('SELECT no,nation,officer_level FROM general WHERE owner=%i', $userID);

if($me['nation'] == 0){
    Json::die([
        'result'=>false,
        'reason'=>'국가에 소속되어 있지 않습니다.',
    ]);
}

if($me['officer_level'] < 5){
    Json::die([
        'result'=>false,
        'reason'=>'수뇌가 아닙니다.',
    ]);
}

if($turnAmount > 0){
    pushNationCommand($me['nation'], $me['officer_level']);
}
else{
    pullNationCommand($me['nation'], $me['officer_level']);
}

Json::die([
    'result'=>true,
    'reason'=>'success',
]);