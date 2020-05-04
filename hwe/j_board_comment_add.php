<?php
namespace sammo;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();

$articleNo = Util::getPost('articleNo', 'int');
$text = Util::getPost('text');

increaseRefresh("회의실", 1);

$me = $db->queryFirstRow('SELECT no, nation, name, officer_level, permission, con, turntime, belong, penalty FROM general WHERE owner=%i', $userID);

$con = checkLimit($me['con']);
if ($con >= 2) {
    Json::die([
        'result'=>false,
        'reason'=>'접속 제한입니다.'
    ]);
}

if($articleNo === null || $text === null){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 입력입니다.'
    ]);
}

$text = StringUtil::neutralize($text);

if(!$text){
    Json::die([
        'result'=>false,
        'reason'=>'내용이 비어있습니다.'
    ]);
}

$article = $db->queryFirstRow('SELECT * FROM board WHERE no = %i AND nation_no = %i', $articleNo, $me['nation']);
if(!$article){
    Json::die([
        'result'=>false,
        'reason'=>'게시물이 없습니다.'
    ]);
}

$isSecretBoard = $article['is_secret'];

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

$db->insert('comment', [
    'nation_no'=>$me['nation'],
    'is_secret'=>$isSecretBoard,
    'date'=>TimeUtil::now(),
    'document_no'=>$articleNo,
    'general_no'=>$me['no'],
    'author'=>$me['name'],
    'text'=>$text
]);

Json::die([
    'result'=>true,
    'reason'=>'success',
    'row_id'=>$db->insertId()
]);