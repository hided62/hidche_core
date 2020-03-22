<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$autorun_user = $gameStor->autorun_user;
if($autorun_user['limit_minutes']??false){
    //자동 턴인 경우에는 휴가 불가
    header('location:b_myPage.php', true, 303);
}

$killturn = $gameStor->killturn;

$db->update('general', [
    'killturn'=>$killturn*3,
], 'owner=%i', $userID);


header('location:b_myPage.php', true, 303);

