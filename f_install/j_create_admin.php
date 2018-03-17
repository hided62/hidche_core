<?php

require('_common.php');
require(__DIR__.'/../f_config/SETTING.php');
require(ROOT.'/f_func/class._Time.php');

use utilphp\util as util;

session_start();
session_destroy();

$username = util::array_get($_POST['username']);
$password = util::array_get($_POST['password']);
$nickname = util::array_get($_POST['nickname']);

if(!$username || !$password || !$nickname){
    returnJson([
        'result'=>false,
        'reason'=>'입력값이 설정되지 않았습니다.'
    ]);
}

if(strlen($password)!=128){
    returnJson([
        'result'=>false,
        'reason'=>'올바르지 않은 비밀번호 해시 포맷입니다.'
    ]);
}

if(!$SETTING->isExist()){
    returnJson([
        'result'=>false,
        'reason'=>'DB 설정이 완료되지 않았습니다.'
    ]);
}

require(__DIR__.'/../f_config/DB.php');
$rootDB = getRootDB();

//초기 관리자 계정은 딱 하나만 있어야하므로, 중요함.
$rootDB->query('LOCK TABLES member WRITE, member_log WRITE');

$memberCnt = $rootDB->queryFirstField('SELECT count(`NO`) from member');
if($memberCnt > 0){
    returnJson([
        'result'=>'false',
        'reason'=>'이미 계정이 생성되어 있습니다'
    ]);
}

$userSalt = bin2hex(random_bytes(8));
$finalPassword = hashPassword($userSalt, $password);
$nowDate = _Time::DatetimeNow();

$rootDB->insert('member',[
    'oauth_type' => 'NONE',
    'id' => $username,
    'email' => null,
    'pw' => $finalPassword,
    'salt' => $userSalt,
    'grade'=> 6,
    'name'=>$nickname,
    'reg_date'=>$nowDate
]);
$userID = $rootDB->insertId();

$rootDB->insert('member_log', [
    'member_no'=>$userID,
    'date'=>$nowDate,
    'action_type'=>'reg',
    'action'=>json_encode([
        'type'=>'none',
        'aux'=>'admin',
        'id'=>$username,
        'name'=>$nickname
    ], JSON_UNESCAPED_UNICODE)
]);

returnJson([
    'result'=>true,
    'reason'=>'success'
]);