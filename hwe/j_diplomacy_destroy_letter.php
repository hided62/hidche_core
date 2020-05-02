<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();

$letterNo = Util::getReq('letterNo', 'int');

increaseRefresh("외교부", 1);

$me = $db->queryFirstRow('SELECT no, name, nation, officer_level, permission, con, turntime, belong, penalty, picture, imgsvr FROM general WHERE owner=%i', $userID);

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

$letter = $db->queryFirstRow('SELECT * FROM ng_diplomacy WHERE no=%i AND (src_nation_id = %i OR dest_nation_id = %i) AND state = \'activated\'', $letterNo, $me['nation'], $me['nation']);
if(!$letter){
    Json::die([
        'result'=>false,
        'reason'=>'서신이 없습니다.'
    ]);
}


$aux = Json::decode($letter['aux']);


$stateOpt = $aux['state_opt']??null;

if(($stateOpt == 'try_destroy_src' && $letter['src_nation_id'] == $me['nation'])||
($stateOpt == 'try_destroy_dest' && $letter['dest_nation_id'] == $me['nation'])){
    Json::die([
        'result'=>false,
        'reason'=>'이미 파기 신청을 했습니다.'
    ]);
}

$srcNation = getNationStaticInfo($letter['src_nation_id']);
$destNation = getNationStaticInfo($letter['dest_nation_id']);
$me['icon'] = GetImageURL($me['imgsvr'], $me['picture']);


if($letter['src_nation_id'] == $me['nation']){
    $src = new MessageTarget($me['no'], $me['name'], $srcNation['nation'], $srcNation['name'], $srcNation['color'], $me['icon']);
    $dest = new MessageTarget(0, '', $destNation['nation'], $destNation['name'], $destNation['color']);
}
else{
    $src = new MessageTarget($me['no'], $me['name'], $destNation['nation'], $destNation['name'], $destNation['color'], $me['icon']);
    $dest = new MessageTarget(0, '', $srcNation['nation'], $srcNation['name'], $srcNation['color']);
}


$now = new \DateTime();
$unlimited = new \DateTime('9999-12-31');

if(in_array($stateOpt, ['try_destroy_src', 'try_destroy_dest'])){
    $deleteLetterNo = $letterNo;
    $db->update('ng_diplomacy', [
        'state'=>'cancelled',
        'aux'=>Json::encode($aux)
    ], 'no=%i', $letterNo);
    while(true){
        $deleteLetter = $db->queryFirstRow('SELECT prev_no, aux FROM ng_diplomacy WHERE no = %i AND state = \'replaced\'', $letterNo);
        if(!$deleteLetter){
            break;
        }
        $deleteAux = Json::decode($deleteLetter['aux']);
        $deleteLetterNo = $deleteLetter['prev_no'];
        $deleteAux['reason'] = [
            'who'=>$me['no'],
            'action'=>'destroy',
            'reason'=>'파기'
        ];
    }
    $msgText = "외교 서신 #{$letterNo}를 파기했습니다.";
    $lastState = 'cancelled';
}
else{
    if ($letter['src_nation_id'] == $me['nation']) {
        $aux['state_opt'] = 'try_destroy_src';
    }
    else{
        $aux['state_opt'] = 'try_destroy_dest';
    }
    $db->update('ng_diplomacy', [
        'aux'=>Json::encode($aux)
    ], 'no=%i', $letterNo);

    $msgText = "외교 서신 #{$letterNo}를 파기 요청합니다.";
    $lastState = 'activated';
}

$msg = new Message(
    Message::MSGTYPE_DIPLOMACY,
    $src,
    $dest,
    $msgText,
    $now,
    $unlimited,
    ['deletable' => false]
);
$msgID = $msg->send();

Json::die([
    'result'=>true,
    'reason'=>'success',
    'state'=>$lastState
]);