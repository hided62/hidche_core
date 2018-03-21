<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_func/class._Session.php');

$SESSION = new _Session();

if(!$SESSION->isLoggedIn()) {
    returnJson([
        'result'=>false,
        'reason'=>'로그인되지 않았습니다.'
    ]);
}

// 외부 파라미터

$db = getRootDB();
$member = $db->queryFirstRow('SELECT `id`, `name`, `grade`, `picture` FROM `MEMBER` WHERE `NO` = %i', $SESSION->NoMember());

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

returnJson([
    'result'=>true,
    'reason'=>'success',
    'id'=>$member['id'],
    'name'=>$member['name'],
    'grade'=>$grade,
    'picture'=>$picture,
    'global_salt'=>getGlobalSalt()
]);
