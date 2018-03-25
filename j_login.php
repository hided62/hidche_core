<?php
namespace sammo;

require_once('_common.php');


$RootDB = RootDB::db();
$session = Session::Instance();
if($session->isLoggedIn()){
    $session->logout();
}

$username = mb_strtolower(Util::array_get($_POST['username']), 'utf-8');
$password = Util::array_get($_POST['password']);

if(!$username || !$password){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 입력입니다.'
    ]);
}

$userInfo = $RootDB->queryFirstRow(
    'SELECT `no`, `id`, `name`, `grade`, `delete_after` '.
    'from member where id=%s_username AND '.
    'pw=sha2(concat(salt, %s_password, salt), 512)',[
        'username'=>$username,
        'password'=>$password
]);

if(!$userInfo){
    Json::die([
        'result'=>false,
        'reason'=>'아이디나 비밀번호가 올바르지 않습니다.'
    ]);
}

$canLogin = $RootDB->queryFirstField('SELECT `LOGIN` FROM `SYSTEM` WHERE `NO` = 1');
if($canLogin != 'Y' && $userInfo['grade'] < 5){
    Json::die([
        'result'=>false,
        'reason'=>'현재는 로그인이 금지되어있습니다!'
    ]);
}

$nowDate = TimeUtil::DatetimeNow();
if($userInfo['delete_after']){
    if($userInfo['delete_after'] < $nowDate){
        $RootDB->delete('member', 'no=%i', $userInfo['no']);
        Json::die([
            'result'=>false,
            'reason'=>"기간 만기로 삭제되었습니다. 재 가입을 시도해주세요."
        ]);
    }
    else{
        Json::die([
            'result'=>false,
            'reason'=>"삭제 요청된 계정입니다.[{$userInfo['delete_after']}]"
        ]);
    }
    
}

$RootDB->insert('member_log',[
    'member_no'=>$userInfo['no'],
    'action_type'=>'login',
    'action'=>Json::encode([
        'ip'=>Util::get_client_ip(true),
        'type'=>'plain'
    ])
]);



$session->login($userInfo['no'], $userInfo['id'], $userInfo['grade']);
Json::die([
    'result'=>true,
    'reason'=>'로그인 되었습니다.'
]);