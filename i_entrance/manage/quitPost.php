<?php
// 외부 파라미터
// $_POST['pw'] : PW
$pw = $_POST['pw'];

require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_FUNC.W.'class._Time.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);
require_once(ROOT.W.F_CONFIG.W.SESSION.PHP);

$response['result'] = 'FAIL';

$rs = $DB->Select('PW', 'MEMBER', "NO='{$SESSION->NoMember()}'");
$member = $DB->Get($rs);

if($member['PW'] != $pw) {
    $response['result'] = 'FAIL';
    $response['msg'] = '실패: 현재 비밀번호가 일치하지 않습니다.';
} else {
    $DB->UpdateArray('MEMBER', array(
        'QUIT'    =>  'Y',
        'REG_DATE'=> _Time::DatetimeNow()
    ), "NO='{$SESSION->NoMember()}'");


    $SESSION->Logout();

    $response['result'] = 'SUCCESS';
    $response['msg'] = "정상적으로 탈퇴신청 되었습니다.";
}

sleep(1);
echo json_encode($response);


