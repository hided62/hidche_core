<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_FUNC.W.'class._Validation.php');
require_once(ROOT.W.F_FUNC.W.'class._Time.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);

// 외부 파라미터
// $_POST['email'] : 이메일
// $_POST['code'] : 인증번호
$email = $_POST['email'];
$code = $_POST['code'];


$response['result'] = 'FAIL';

$err = _Validation::CheckEmail($email);
if($err == 1) {
    $response['result'] = 'FAIL';
    $response['msg'] = '이메일이 올바르지 않습니다!';
} elseif($err == 0) {
    $rs = $DB->Select('CODE', 'EMAIL', "EMAIL='{$email}'");
    $count = $DB->Count($rs);

    if($count == 0) {
        $response['result'] = 'FAIL';
        $response['msg'] = '인증번호 전송 내역이 없습니다.';
    } elseif($count == 1) {
        $result = $DB->Get($rs);

        if($result['CODE'] != $code) {
            $response['result'] = 'FAIL';
            $response['msg'] = '인증번호가 일치하지 않습니다.';
        } else {
            $DB->UpdateArray('EMAIL', array(
                'VERIFIED'=>  1,
                'VRF_DATE'=> _Time::DatetimeNow()
            ), "EMAIL='{$email}'");

            $response['result'] = 'SUCCESS';
            $response['msg'] = '인증번호가 확인되었습니다.';
        }
    }
}

sleep(1);
echo json_encode($response);


