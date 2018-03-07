<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._JSON.php');
require_once(ROOT.'/f_func/class._Validation.php');
require_once(ROOT.'/f_func/class._Time.php');
require_once(ROOT.'/f_config/DB.php');

// 외부 파라미터
// $_POST['email'] : 이메일
// $_POST['code'] : 인증번호
$email = $_POST['email'];
$code = $_POST['code'];


$response['result'] = 'FAIL';

$db = getRootDB();

$err = _Validation::CheckEmail($email);
if($err == 1) {
    $response['result'] = 'FAIL';
    $response['msg'] = '이메일이 올바르지 않습니다!';
} elseif($err == 0) {
    $cmpCode = $db->queryFirstField('SELECT CODE FROM EMAIL WHERE EMAIL = %s', $email);

    if(!$cmpCode) {
        $response['result'] = 'FAIL';
        $response['msg'] = '인증번호 전송 내역이 없습니다.';
    } else {
        if($cmpCode != $code) {
            $response['result'] = 'FAIL';
            $response['msg'] = '인증번호가 일치하지 않습니다.';
        } else {
            $db->update('EMAIL', [
                'VERIFIED'=>  1,
                'VRF_DATE'=> _Time::DatetimeNow()
            ], 'EMAIL=%s', $email);

            $response['result'] = 'SUCCESS';
            $response['msg'] = '인증번호가 확인되었습니다.';
        }
    }
}

sleep(1);
echo json_encode($response);


