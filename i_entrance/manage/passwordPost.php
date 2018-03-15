<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

// 외부 파라미터
// $_POST['pw'] : PW
// $_POST['newPw'] : 새 PW
$pw = $_POST['pw'];
$newPw = $_POST['newPw'];

$response['result'] = 'FAIL';

$db = getRootDB();
$member = $db->queryFirstRow('SELECT `ID`, `PW` FROM `MEMBER` WHERE `NO` = %i', $SESSION->NoMember());

if($member['PW'] != $pw) {
    $response['result'] = 'FAIL';
    $response['msg'] = '실패: 현재 비밀번호가 일치하지 않습니다.';
} else {
    $db->update('MEMBER', ['PW'=>$newPw], 'NO=%i', $SESSION->NoMember());

    $response['result'] = 'SUCCESS';
    $response['msg'] = "정상적으로 비밀번호가 수정되었습니다.";
}

sleep(1);
echo json_encode($response);


