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

increaseRefresh("회의실", 1);

$me = $db->queryFirstRow('SELECT no, nation, officer_level, permission, con, turntime, belong, penalty FROM general WHERE owner=%i', $userID);


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
else if ($isSecretBoard && $permission < 2) {
    Json::die([
        'result'=>false,
        'reason'=>'권한이 부족합니다. 수뇌부가 아닙니다.'
    ]);
}

$articles = [];

foreach(
    $db->query(
        'SELECT * FROM board WHERE nation_no = %i AND is_secret = %i ORDER BY date desc',
        $me['nation'],
        $isSecretBoard
    ) as $article
){
    //TODO:아이콘 받아오기
    $article['comment'] = [];
    $articles[$article['no']] = $article;
}

foreach(
    $db->query(
        'SELECT * FROM comment WHERE nation_no = %i AND is_secret = %i ORDER BY date asc',
        $me['nation'],
        $isSecretBoard
    ) as $comment
){
    //TODO:아이콘 받아오기?
    $articles[$comment['document_no']]['comment'][] = $comment;
}

Json::die([
    'result'=>true,
    'articles'=>$articles,
    'reason'=>'success'
]);

