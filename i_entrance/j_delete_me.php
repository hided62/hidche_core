<?php
namespace sammo;

require(__DIR__.'/../vendor/autoload.php');

$session = Session::requireLogin([]);
$userID = Session::getUserID();

// 외부 파라미터
// $_POST['pw'] : PW
$pw = Util::getReq('pw');

if(!$pw){
    Json::die([
        'result'=>false,
        'reason'=>'패스워드를 입력해주세요.'
    ]);
}

//TODO: 탈퇴 처리하되 한달간 유지.
$db = RootDB::db();

$userInfo = $db->queryFirstRow('SELECT oauth_id, oauth_type, email, delete_after FROM member '.
    'WHERE `no`=%i and pw=sha2(concat(salt, %s, salt), 512)',
    $userID, $pw);

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
    'delete_after'=>TimeUtil::nowAddMinutes(60*24*30)
], 'no=%i', $userID);

if(!$db->affectedRows()){
    Json::die([
        'result'=>false,
        'reason'=>'알 수 없는 이유로 탈퇴에 실패했습니다.'
    ]);
}



$db->insert('member_log', [
    'member_no'=>$userID,
    'action_type'=>'delete'
]);


$session->access_token = null;
$session->logout();
setcookie("hello", "", time()-3600);

Json::die([
    'result'=>true,
    'reason'=>'success'
]);
