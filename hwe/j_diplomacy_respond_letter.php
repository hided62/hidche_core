<?php
namespace sammo;

use sammo\Enums\MessageType;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();

$letterNo = Util::getPost('letterNo', 'int');
$isAgree  = Util::getPost('isAgree', 'bool', false);
$reason = Util::getPost('reason', 'string', '');




increaseRefresh("외교부", 1);

$me = $db->queryFirstRow(
    'SELECT no, name, nation, officer_level, permission, refresh_score, turntime, belong, penalty, picture, imgsvr FROM `general`
    LEFT JOIN general_access_log AS l ON `general`.no = l.general_id WHERE owner=%i', $userID
);

$limitState = checkLimit($me['refresh_score']);
if ($limitState >= 2) {
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
$me['icon'] = GetImageURL($me['imgsvr'], $me['picture']);

$srcNation = getNationStaticInfo($letter['src_nation_id']);
$destNation = getNationStaticInfo($letter['dest_nation_id']);

$src = new MessageTarget($me['no'], $me['name'], $destNation['nation'], $destNation['name'], $destNation['color'], $me['icon']);
$dest = new MessageTarget(0, '', $srcNation['nation'], $srcNation['name'], $srcNation['color']);

$now = new \DateTime();
$unlimited = new \DateTime('9999-12-31');

if($isAgree){
    $aux['dest']['generalName'] = $me['name'];
    $aux['dest']['generalIcon'] = $me['icon'];

    $db->update('ng_diplomacy', [
        'state'=>'activated',
        'dest_signer'=>$me['no'],
        'aux'=>Json::encode($aux)
    ], 'no=%i', $letterNo);
    //TODO: 외교 서신에 대한 메시지를 양국에 발송해야함

    $prevLetterNo = $letter['prev_no'];
    while($prevLetterNo !== null){
        $db->update('ng_diplomacy', [
            'state'=>'replaced',
        ], 'no=%i', $prevLetterNo);
        $prevLetterNo = $db->queryFirstField('SELECT prev_no FROM ng_diplomacy WHERE state != \'cancelled\' AND no = %i', $prevLetterNo);
    }
    $msgText = "외교 서신( #{$letterNo})이 승인되었습니다.";
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
    ], 'no=%i', $letterNo);
    $msgText = "외교 서신(#{$letterNo})이 거부되었습니다.";
    if($reason){
        $msgText .= ' 이유 : '.$reason;
    }
}

$msg = new Message(
    MessageType::diplomacy,
    $src,
    $dest,
    $msgText,
    $now,
    $unlimited,
    ['deletable' => false]
);
$msgID = $msg->send();

$msg = new Message(
    MessageType::national,
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
    'reason'=>'success'
]);