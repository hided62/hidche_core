<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._Time.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

// 외부 파라미터
// $_POST['pw'] : PW
$pw = $_POST['pw'];

$response['result'] = 'FAIL';
//TODO: 즉시 탈퇴 처리하되, 
$db = getRootDB();
$member = $db->queryFirstRow('SELECT `PW` FROM `MEMBER` WHERE `NO` = %i', $SESSION->NoMember());

if($member['PW'] != $pw) {
    $response['result'] = 'FAIL';
    $response['msg'] = '실패: 현재 비밀번호가 일치하지 않습니다.';
} else {
    $db->update('MEMBER', array(
        'QUIT'    =>  'Y',
        'REG_DATE'=> _Time::DatetimeNow()
    ), 'NO=%i', $SESSION->NoMember());


    $SESSION->Logout();

    $response['result'] = 'SUCCESS';
    $response['msg'] = "정상적으로 탈퇴신청 되었습니다.";
}

sleep(1);
echo json_encode($response);


