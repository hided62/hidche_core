<?php
include('lib.php');
include('func.php');

$userID = getUserID();
$generalID = getGeneralID();
session_write_close();

if(!$userID){
    returnJson([
        'result'=>false,
        'reason'=>'로그인되지 않았습니다.'
    ]);
}

if(!$generalID){
    returnJson([
        'result'=>false,
        'reason'=>'장수를 생성하지 않았습니다.'
    ]);
}

$rootDB = getRootDB();
$db = getDB();

$image = $rootDB->queryFirstRow('SELECT picture, imgsvr FROM `MEMBER` WHERE no = %i', $userID);

if(!$image){
    returnJson([
        'result'=>false,
        'reason'=>'회원 기록 정보가 없습니다'
    ]);
}

$db->update('general', [
    'picture'=>$image['picture'],
    'imgsvr'=>$image['imgsvr']
], 'owner = %i and npc = 0', $userID);

returnJson([
    'result'=>true,
    'reason'=>'success'
]);