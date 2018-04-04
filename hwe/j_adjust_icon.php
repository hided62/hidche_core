<?php
namespace sammo;

include('lib.php');

$session = Session::requireGameLogin([])->setReadOnly();
$userID = $session->userID;
$generalID = $session->generalID;

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

$affected = $db->affectedRows();
if($affected == 0){
    Json::die([
        'result'=>true,
        'reason'=>'등록된 장수가 없습니다'
    ]);    
}

Json::die([
    'result'=>true,
    'reason'=>'success'
]);