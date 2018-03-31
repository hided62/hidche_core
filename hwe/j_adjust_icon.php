<?php
namespace sammo;

include('lib.php');
include('func.php');

$userID = Session::getUserID();
$generalID = Session::Instance()->generalID;
session_write_close();

if(!$userID){
    Json::die([
        'result'=>false,
        'reason'=>'로그인되지 않았습니다.'
    ]);
}

if(!$generalID){
    Json::die([
        'result'=>false,
        'reason'=>'장수를 생성하지 않았습니다.'
    ]);
}

$rootDB = RootDB::db();
$db = DB::db();

$image = $rootDB->queryFirstRow('SELECT picture, imgsvr FROM `MEMBER` WHERE no = %i', $userID);

if(!$image){
    Json::die([
        'result'=>false,
        'reason'=>'회원 기록 정보가 없습니다'
    ]);
}

$db->update('general', [
    'picture'=>$image['picture'],
    'imgsvr'=>$image['imgsvr']
], 'owner = %i and npc = 0', $userID);

Json::die([
    'result'=>true,
    'reason'=>'success'
]);