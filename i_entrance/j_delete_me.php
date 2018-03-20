<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._Time.php');
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
// $_POST['pw'] : PW
$pw = $_POST['pw'];

if(!$pw){
    returnJson([
        'result'=>false,
        'reason'=>'패스워드를 입력해주세요.'
    ]);
}

//TODO: 탈퇴 처리하되 한달간 유지.
$db = getRootDB();

$userInfo = $db->queryFirstRow('SELECT oauth_id, oauth_type, email, delete_after FROM MEMBER '.
    'WHERE `no`=%i and pw=sha2(concat(salt, %s, salt), 512)',
    $SESSION->NoMember(), $pw);

if(!$userInfo){
    returnJson([
        'result'=>false,
        'reason'=>'현재 비밀번호가 일치하지 않습니다.'
    ]);
}

if($userInfo['delete_after']){
    returnJson([
        'result'=>false,
        'reason'=>'이미 탈퇴 처리되어있습니다.'
    ]);
}

$db->update('member',[
    'delete_after'=>_Time::DatetimeFromNowMinute(60*24*30)
], 'no=%i', $SESSION->NoMember());

if(!$db->affectedRows()){
    returnJson([
        'result'=>false,
        'reason'=>'알 수 없는 이유로 탈퇴에 실패했습니다.'
    ]);
}



$db->insert('member_log', [
    'member_no'=>$SESSION->NoMember(),
    'action_type'=>'delete'
]);

$SESSION->logout();
unset($_SESSION['access_token']);
setcookie("hello", "", time()-3600);

returnJson([
    'result'=>true,
    'reason'=>'success'
]);
