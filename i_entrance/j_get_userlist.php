<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

$session = Session::requireLogin([])->setReadOnly();
$db = RootDB::db();

if($session->userGrade < 5){
    Json::die([
        'result'=>false,
        'reason'=>'권한이 부족합니다'
    ]);
}

$userList = [];
foreach($db->query('SELECT member.*, max(member_log.date) as loginDate from member 
        left join member_log on member.`NO` = member_log.member_no and member_log.action_type="login"
        group by member.no order by member.no asc') as $member){

    if($member['IMGSVR']){
        $icon = AppConf::getUserIconPathWeb().'/'.$member['PICTURE'];
    }
    else{
        $icon = ServConfig::getSharedIconPath().'/'.$member['PICTURE'];
    }

    $userList[] = [
        'userID'=>$member['NO'],
        'userName'=>$member['ID'],
        'email'=>$member['EMAIL'],
        'authType'=>$member['oauth_type'],
        'userGrade'=>$member['GRADE'],
        'blockUntil'=>$member['BLOCK_DATE'],
        'nickname'=>$member['NAME'],
        'icon'=>$icon,
        'joinDate'=>$member['REG_DATE'],
        'loginDate'=>$member['loginDate'],
        'deleteAfter'=>$member['delete_after']
    ];
}

$serverList = [];
foreach(AppConf::getList() as $serverName => $setting){
    if($setting->isRunning()){
        $serverList[] = $serverName;
    }
}

$system = $db->queryFirstRow('SELECT `REG`, `LOGIN` FROM `system` limit 1');

Json::die([
    'result'=>true,
    'users'=>$userList,
    'servers'=>$serverList,
    'allowJoin'=>$system['REG']=='Y',
    'allowLogin'=>$system['LOGIN']=='Y'
]);