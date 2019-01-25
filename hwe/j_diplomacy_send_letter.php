<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();

$destNationNo = Util::getReq('destNation', 'int', 0);
$prevNo = Util::getReq('prevNo', 'int', null);
$textBrief = Util::getReq('brief');
$textDetail = Util::getReq('detail');

increaseRefresh("외교부", 1);

if($prevNo < 1){
    $prevNo = null;
}

$me = $db->queryFirstRow('SELECT no, name, nation, level, permission, con, turntime, belong, penalty, picture, imgsvr FROM general WHERE owner=%i', $userID);

$con = checkLimit($me['con']);
if ($con >= 2) {
    Json::die([
        'result'=>false,
        'reason'=>'접속 제한입니다.'
    ]);
}

if($destNationNo == $me['nation']){
    Json::die([
        'result'=>false,
        'reason'=>'자국으로 보낼 수 없습니다.'
    ]);
}

if($textBrief === null || $textDetail === null){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 입력입니다.'
    ]);
}

$textBrief = trim($textBrief);
$textDetail = trim($textDetail);

if(!$textBrief){
    Json::die([
        'result'=>false,
        'reason'=>'요약문이 비어있습니다'
    ]);
}


$permission = checkSecretPermission($me);
if ($permission < 4) {
    Json::die([
        'result'=>false,
        'reason'=>'권한이 부족합니다. 수뇌부가 아닙니다.'
    ]);
}

$srcNationNo = $me['nation'];

if($prevNo !== null){
    //state는 체크하지 않는걸로 하자. 파기한 것을 재 송신하는 경우도 있을 수 있음.
    $prevLetter = $db->queryFirstRow(
        'SELECT no, src_nation_id, dest_nation_id, state, aux FROM ng_diplomacy WHERE no = %i AND src_nation_id IN (%i, %i) AND dest_nation_id IN (%i, %i)',
        $prevNo,
        $srcNationNo, $destNationNo,
        $srcNationNo, $destNationNo
    );

    if(!$prevLetter){
        Json::die([
            'result'=>false,
            'reason'=>'이전 문서가 없습니다.'
        ]);
    }

    //새로 나온 문서가 있는지 확인하자
    $newerLetter = $db->queryFirstField(
        'SELECT count(no) FROM ng_diplomacy WHERE prev_no = %i AND state != \'cancelled\'', $prevNo
    );
    if($newerLetter){
        Json::die([
            'result'=>false,
            'reason'=>'해당 문서에 대한 새로운 문서가 이미 있습니다.'
        ]);
    }

    if($prevLetter['src_nation_id'] != $srcNationNo){
        $destNationNo = $prevLetter['src_nation_id'];
    }
    else{
        $destNationNo = $prevLetter['dest_nation_id'];
    }

    if($prevLetter['state'] == 'proposed'){
        $prevAux = Json::decode($prevLetter['aux']);
        $prevAux['reason'] = [
            'who'=>$me['no'],
            'action'=>'new_letter',
            'reason'=>'new_letter'
        ];
        $db->update('ng_diplomacy', [
            'state'=>'replaced',
            'aux'=>Json::encode($prevAux)
        ], 'no=%i', $prevNo);
    }
}

$nations = $db->query('SELECT nation, name, color FROM nation WHERE nation IN (%i, %i)', $srcNationNo, $destNationNo);
if(count($nations) != 2){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 국가입니다.'
    ]);
}

if($nations[0]['nation'] == $me['nation']){
    //index 순서에 따라 또 모름.
    $srcNation = $nations[0];
    $destNation = $nations[1];
}
else{
    $srcNation = $nations[1];
    $destNation = $nations[0];
}

$me['icon'] = GetImageURL($me['imgsvr'], $me['picture']);

$db->insert('ng_diplomacy', [
    'src_nation_id'=>$srcNation['nation'],
    'dest_nation_id'=>$destNation['nation'],
    'prev_no'=>$prevNo,
    'state'=>'proposed',
    'text_brief'=>$textBrief,
    'text_detail'=>$textDetail,
    'date'=>TimeUtil::DatetimeNow(),
    'src_signer'=>$me['no'],
    'dest_signer'=>null,
    'aux'=>Json::encode([
        'src'=>[
            'nationName'=>$srcNation['name'],
            'nationColor'=>$srcNation['color'],
            'generalName'=>$me['name'],
            'generalIcon'=>$me['icon']
        ],
        'dest'=>[
            'nationName'=>$destNation['name'],
            'nationColor'=>$destNation['color']
        ]
    ]),
]);
$newLetterNo = $db->insertId();

$src = new MessageTarget($me['no'], $me['name'], $srcNation['nation'], $srcNation['name'], $srcNation['color'], $me['icon']);
$dest = new MessageTarget(0, '', $destNation['nation'], $destNation['name'], $destNation['color']);

$now = new \DateTime();
$unlimited = new \DateTime('9999-12-31');

$josaYi = JosaUtil::pick($newLetterNo, '이');
if($prevNo){
    $msgText = "문서 #{$prevNo}의 새로운 외교 문서 #{$newLetterNo}{$josaYi} 준비되었습니다. 외교부에서 확인해주세요.";
}
else{
    $msgText = "새로운 외교 문서 #{$newLetterNo}{$josaYi} 준비되었습니다. 외교부에서 확인해주세요.";
}


$msg = new Message(
    Message::MSGTYPE_DIPLOMACY,
    $src,
    $dest,
    $msgText,
    $now,
    $unlimited,
    ['invalid' => true]
);
$msgID = $msg->send();

Json::die([
    'result'=>true,
    'reason'=>'success',
    'row_id'=>$db->insertId()
]);