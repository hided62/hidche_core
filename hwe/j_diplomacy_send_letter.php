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
$textBrief = Util::getReq('textBrief');
$textDetail = Util::getReq('textDetail');

increaseRefresh("외교부", 1);

if($prevNo < 1){
    $prevNo = null;
}

$me = $db->queryFirstRow('SELECT no, nation, level, permission, con, turntime, belong, penalty FROM general WHERE owner=%i', $userID);

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

$nations = $db->query('SELECT nation, name, color FROM nation WHERE nation IN (%i, %i)', $srcNationNo, $destNationNo);
if(count($nations) != 2){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 국가입니다.'
    ]);
}

if($prevNo !== null){
    //state는 체크하지 않는걸로 하자. 파기한 것을 재 송신하는 경우도 있을 수 있음.
    $prevLetter = $db->queryFirstRow(
        'SELECT no, state, aux FROM ng_diplomacy WHERE no = %i AND src_nation_id IN (%i, %i) AND dest_nation_id IN (%i, %i)',
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

    if($prevLetter['state'] == 'proposed'){
        $prevAux = Json::decode($prevLetter['aux']);
        $prevAux['reason'] = [
            'who'=>$me['no'],
            'action'=>'new_letter',
            'reason'=>'new_letter'
        ];
        $db->update('ng_diplomacy', [
            'state'=>'cancelled',
            'aux'=>Json::encode($prevAux)
        ], 'no=%i', $prevNo);
    }
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
            'generalName'=>$me['name']
        ],
        'dest'=>[
            'nationName'=>$destNation['name'],
            'nationColor'=>$destNation['color']
        ]
    ]),
]);


//TODO: 외교 서신에 대한 메시지를 양국에 발송해야함

Json::die([
    'result'=>true,
    'reason'=>'success',
    'row_id'=>$db->insertId()
]);