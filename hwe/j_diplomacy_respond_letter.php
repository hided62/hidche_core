<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();

$letterNo = Util::getReq('letterNo', 'int');
$isAgree  = Util::getReq('isAgree', 'bool', false);
$reason = Util::getReq('reason', 'string', '');




increaseRefresh("회의실", 1);

$me = $db->queryFirstRow('SELECT no, nation, level, permission, con, turntime, belong, penalty FROM general WHERE owner=%i', $userID);

$con = checkLimit($me['con']);
if ($con >= 2) {
    Json::die([
        'result'=>false,
        'reason'=>'접속 제한입니다.'
    ]);
}

if($letterNo === null){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 입력입니다.'
    ]);
}


$permission = checkSecretPermission($me);
if ($permission < 4) {
    Json::die([
        'result'=>false,
        'reason'=>'권한이 부족합니다. 수뇌부가 아닙니다.'
    ]);
}

$reason = trim($reason);

$letter = $db->queryFirstRow('SELECT * FROM ng_diplomacy WHERE no=%i AND dest_nation_id = %i AND state = \'proposed\'', $letterNo, $me['nation']);
if(!$letter){
    Json::die([
        'result'=>false,
        'reason'=>'서신이 없습니다.'
    ]);
}

$aux = Json::decode($letter['aux']);

if($isAgree){
    $aux['dest']['generalName'] = $me['name'];
    $db->update('ng_diplomacy', [
        'state'=>'activated',
        'dest_signer'=>$me['no'],
        'aux'=>Json::encode($aux)
    ], 'no=%i', $letterNo);
    //TODO: 외교 서신에 대한 메시지를 양국에 발송해야함
}
else{
    $aux['reason'] = [
        'who'=>$me['no'],
        'action'=>'disagree',
        'reason'=>$reason
    ];
    $db->update('ng_diplomacy', [
        'state'=>'cancelled',
        'aux'=>Json::encode($aux)
    ]);
    //TODO: 외교 서신에 대한 메시지를 양국에 발송해야함
}

Json::die([
    'result'=>true,
    'reason'=>'success'
]);