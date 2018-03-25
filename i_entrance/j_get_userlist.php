<?php
namespace sammo;

require_once('_common.php');

$session = Session::requireLogin();
$db = RootDB::db();

$session->setReadOnly();

if($session->userGrade < 6){
    Json::die([
        'result'=>false,
        'reason'=>'권한이 부족합니다'
    ]);
}

$userList = [];
foreach($db->query('SELECT * FROM MEMBER order by `no`') as $member){
    $userID = $member['NO'];
    $userName = $member['ID'];
    $email = $member['EMAIL'];
    $grade = $member['GRADE'];
    $blockUntil = $member['BLOCK_DATE'];

    $nickname = $member['NAME'];
    if($member['IMGSVR']){
        $icon = RootDB::getServerBasepath().'/'.$member['PICTURE'];
    }
    else{
        $icon = IMAGES.'/'.$member['PICTURE'];
    }
    
    $deleteAfter = $member['delete_after'];
    
}

$serverList = [];
foreach(AppConf::getList() as $serverName => $setting){
    
}