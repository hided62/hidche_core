<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);
require_once(ROOT.W.F_CONFIG.W.'DBS'.PHP);
require_once(ROOT.W.F_CONFIG.W.SETTINGS.PHP);
require_once(ROOT.W.F_CONFIG.W.SESSION.PHP);

// 외부 파라미터
// $_POST['pw'] : PW
// $_POST['newPw'] : 새 PW
$pw = $_POST['pw'];
$newPw = $_POST['newPw'];

$response['result'] = 'FAIL';

$rs = $DB->Select('ID, PW', 'MEMBER', "NO='{$SESSION->NoMember()}'");
$member = $DB->Get($rs);

if($member['PW'] != $pw) {
    $response['result'] = 'FAIL';
    $response['msg'] = '실패: 현재 비밀번호가 일치하지 않습니다.';
} else {
    $DB->Update('MEMBER', "PW='{$newPw}'", "NO='{$SESSION->NoMember()}'");

    for($i=0; $i < $_serverCount; $i++) {
        if($SETTINGS[$i]->IsExist()) {
            $DBS[$i]->Update('general', "PASSWORD='{$newPw}'", "USER_ID='{$member['ID']}'");
        }
    }

    $response['result'] = 'SUCCESS';
    $response['msg'] = "정상적으로 비밀번호가 수정되었습니다.";
}

sleep(1);
echo json_encode($response);


