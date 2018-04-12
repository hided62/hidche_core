<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

$session = Session::requireLogin([])->setReadOnly();
$userID = Session::getUserID();

// 외부 파라미터
// $_POST['old_pw'] : PW
// $_POST['new_pw'] : 새 PW
$pw = $_POST['old_pw'];
$newPw = $_POST['new_pw'];

$response = ['result' => false];

$db = RootDB::db();

$userInfo = $db->update('member',[
    'pw'=>$db->sqleval('sha2(concat(salt, %s, salt), 512)', $newPw)
], 'no=%i and pw=sha2(concat(salt, %s, salt), 512)', $userID, $pw);

if(!$db->affectedRows()){
    $db->insert('member_log', [
        'member_no'=>$userID,
        'action_type'=>'change_pw',
        'action'=>Json::encode([
            'type'=>'plain',
            'result'=>false
        ])
    ]);

    Json::die([
        'result'=>false,
        'reason'=>'현재 비밀번호가 일치하지 않습니다.'
    ]);
}

$db->insert('member_log', [
    'member_no'=>$userID,
    'action_type'=>'change_pw',
    'action'=>Json::encode([
        'type'=>'plain',
        'result'=>true
    ])
]);

Json::die([
    'result'=>true,
    'reason'=>'success'
]);