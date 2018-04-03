<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

session_start();
session_destroy();

$username = mb_strtolower(Util::array_get($_POST['username']), 'utf-8');
$password = Util::array_get($_POST['password']);
$nickname = Util::array_get($_POST['nickname']);

if(!$username || !$password || !$nickname){
    Json::die([
        'result'=>false,
        'reason'=>'입력값이 설정되지 않았습니다.'
    ]);
}

if(strlen($password)!=128){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 비밀번호 해시 포맷입니다.'
    ]);
}

if(!class_exists('\\sammo\\RootDB')){
    Json::die([
        'result'=>false,
        'reason'=>'DB 설정이 완료되지 않았습니다.'
    ]);
}

$rootDB = RootDB::db();

//초기 관리자 계정은 딱 하나만 있어야하므로, 중요함.
$rootDB->query('LOCK TABLES member WRITE, member_log WRITE');

$memberCnt = $rootDB->queryFirstField('SELECT count(`NO`) from member');
if($memberCnt > 0){
    Json::die([
        'result'=>'false',
        'reason'=>'이미 계정이 생성되어 있습니다'
    ]);
}

$userSalt = bin2hex(random_bytes(8));
$finalPassword = Util::hashPassword($userSalt, $password);
$nowDate = TimeUtil::DatetimeNow();

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
    'action'=>Json::encode([
        'type'=>'none',
        'aux'=>'admin',
        'id'=>$username,
        'name'=>$nickname
    ])
]);

Json::die([
    'result'=>true,
    'reason'=>'success'
]);