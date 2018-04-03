<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

$session = Session::Instance();

if(!$session->isLoggedIn()) {
    Json::die([
        'result'=>false,
        'reason'=>'로그인되지 않았습니다.'
    ]);
}

// 외부 파라미터
// $_POST['pw'] : PW
$pw = $_POST['pw'];

if(!$pw){
    Json::die([
        'result'=>false,
        'reason'=>'패스워드를 입력해주세요.'
    ]);
}

//TODO: 탈퇴 처리하되 한달간 유지.
$db = RootDB::db();

$userInfo = $db->queryFirstRow('SELECT oauth_id, oauth_type, email, delete_after FROM MEMBER '.
    'WHERE `no`=%i and pw=sha2(concat(salt, %s, salt), 512)',
    $session->userID, $pw);

if(!$userInfo){
    Json::die([
        'result'=>false,
        'reason'=>'현재 비밀번호가 일치하지 않습니다.'
    ]);
}

if($userInfo['delete_after']){
    Json::die([
        'result'=>false,
        'reason'=>'이미 탈퇴 처리되어있습니다.'
    ]);
}

$db->update('member',[
    'delete_after'=>TimeUtil::DatetimeFromNowMinute(60*24*30)
], 'no=%i', $session->userID);

if(!$db->affectedRows()){
    Json::die([
        'result'=>false,
        'reason'=>'알 수 없는 이유로 탈퇴에 실패했습니다.'
    ]);
}



$db->insert('member_log', [
    'member_no'=>$session->userID,
    'action_type'=>'delete'
]);

$session->logout();
unset($_SESSION['access_token']);
setcookie("hello", "", time()-3600);

Json::die([
    'result'=>true,
    'reason'=>'success'
]);
