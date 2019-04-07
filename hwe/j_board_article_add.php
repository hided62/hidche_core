<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();

$isSecretBoard = Util::getReq('isSecret', 'bool', false);
$title = Util::getReq('title');
$text = Util::getReq('text');

increaseRefresh("회의실", 1);

$me = $db->queryFirstRow('SELECT no, nation, name, level, permission, con, turntime, belong, penalty FROM general WHERE owner=%i', $userID);

$con = checkLimit($me['con']);
if ($con >= 2) {
    Json::die([
        'result'=>false,
        'reason'=>'접속 제한입니다.'
    ]);
}

if($title === null || $text === null){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 입력입니다.'
    ]);
}

$title = trim($title);
$text = trim($text);

if(!$title && !$text){
    Json::die([
        'result'=>false,
        'reason'=>'제목과 내용이 둘다 비어있습니다.'
    ]);
}

$permission = checkSecretPermission($me);
if($permission < 0){
    Json::die([
        'result'=>false,
        'reason'=>'국가에 소속되어있지 않습니다.'
    ]);
    
}
else if ($isSecretBoard && $permission < 2) {
    Json::die([
        'result'=>false,
        'reason'=>'권한이 부족합니다. 수뇌부가 아닙니다.'
    ]);
}

$db->insert('board', [
    'nation_no'=>$me['nation'],
    'is_secret'=>$isSecretBoard,
    'date'=>TimeUtil::DatetimeNow(),
    'general_no'=>$me['no'],
    'author'=>$me['name'],
    'title'=>$title,
    'text'=>$text
]);

Json::die([
    'result'=>true,
    'reason'=>'success',
    'row_id'=>$db->insertId()
]);