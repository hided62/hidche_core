<?php
namespace sammo;

require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');

$SESSION = new Session();

if(!$SESSION->isLoggedIn()) {
    Json::die([
        'result'=>false,
        'reason'=>'로그인되지 않았습니다.'
    ]);
}

session_write_close();

// 외부 파라미터
// $_POST['old_pw'] : PW
// $_POST['new_pw'] : 새 PW
$pw = $_POST['old_pw'];
$newPw = $_POST['new_pw'];

$response['result'] = false;

$db = getRootDB();

$userInfo = $db->update('member',[
    'pw'=>$db->sqleval('sha2(concat(salt, %s, salt), 512)', $newPw)
], 'no=%i and pw=sha2(concat(salt, %s, salt), 512)', $SESSION->noMember(), $pw);

if(!$db->affectedRows()){
    $db->insert('member_log', [
        'member_no'=>$SESSION->noMember(),
        'action_type'=>'change_pw',
        'action'=>json_encode([
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
    'member_no'=>$SESSION->noMember(),
    'action_type'=>'change_pw',
    'action'=>json_encode([
        'type'=>'plain',
        'result'=>true
    ])
]);

Json::die([
    'result'=>true,
    'reason'=>'success'
]);