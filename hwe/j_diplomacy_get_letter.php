<?php
namespace sammo;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$isSecretBoard = Util::getPost('isSecret', 'bool', false);

increaseRefresh("외교부", 1);

$me = $db->queryFirstRow(
    'SELECT no, nation, officer_level, permission, refresh_score, turntime, belong, penalty FROM `general`
    LEFT JOIN general_access_log AS l ON `general`.no = l.general_id WHERE owner=%i', $userID
);


$limitState = checkLimit($me['refresh_score']);
if ($limitState >= 2) {
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
        'SELECT * FROM ng_diplomacy WHERE (src_nation_id = %i OR dest_nation_id = %i) AND state != \'cancelled\' ORDER BY date desc',
        $me['nation'], $me['nation']
    ) as $letter
){

    if($permission < 3 && $letter['text_detail']){
        $letter['text_detail'] = '(권한이 부족합니다)';
    }
    $letter['aux'] = Json::decode($letter['aux']);
    $letter['src'] = $letter['aux']['src'];
    $letter['dest'] = $letter['aux']['dest'];

    $letter['src']['nationID'] = $letter['src_nation_id'];
    $letter['dest']['nationID'] = $letter['dest_nation_id'];

    $letters[$letter['no']] = [
        'no'=>$letter['no'],
        'src'=>$letter['src'],
        'dest'=>$letter['dest'],
        'prev_no'=>$letter['prev_no'],
        'state'=>$letter['state'],
        'state_opt'=>($letter['aux']['state_opt']??null),
        'brief'=>$letter['text_brief'],
        'detail'=>$letter['text_detail'],
        'date'=>$letter['date']
    ];
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
    'myNationID'=>$me['nation'],
    'reason'=>'success'
]);
