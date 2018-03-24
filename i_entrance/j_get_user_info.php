<?php
namespace sammo;

require_once('_common.php');

$session = Session::Instance();

if(!$session->isLoggedIn()) {
    Json::die([
        'result'=>false,
        'reason'=>'로그인되지 않았습니다.'
    ]);
}

// 외부 파라미터

$db = RootDB::db();
$member = $db->queryFirstRow('SELECT `id`, `name`, `grade`, `picture` FROM `MEMBER` WHERE `NO` = %i', $session->userID);

if(!$member['picture']){
    $picture = IMAGE.'/default.jpg';
}
else{
    $picture = $member['picture'];
    if(strlen($picture) > 11){
        $picture = substr($picture, 0, -10);
    }
    $picture = '../d_pic/'.$picture;
    if(!file_exists($picture)){
        $picture = IMAGE.'/'.$picture;
    }
}

if($member['grade'] == 6) {
    $grade = '운영자';
} elseif($member['grade'] == 5) {
    $grade = '부운영자';
} elseif($member['grade'] > 1) {
    $grade = '특별회원';
} elseif($member['grade'] == 1) {
    $grade = '일반회원';
} elseif($member['grade'] == 0) {
    $grade = '블럭회원';
}

Json::die([
    'result'=>true,
    'reason'=>'success',
    'id'=>$member['id'],
    'name'=>$member['name'],
    'grade'=>$grade,
    'picture'=>$picture,
    'global_salt'=>RootDB::getGlobalSalt()
]);
