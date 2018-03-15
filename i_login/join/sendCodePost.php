<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._String.php');
require_once(ROOT.'/f_func/class._Validation.php');
require_once(ROOT.'/f_func/class._Time.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/MAIL.php');

// 외부 파라미터
// $_POST['email'] : 이메일
$email = $_POST['email'];

$response['result'] = 'FAIL';

$err = _Validation::CheckEmail($email);
if($err == 1) {
    $response['result'] = 'FAIL';
    $response['msg'] = '이메일이 올바르지 않습니다!';
} elseif($err == 0) {
    $code = _String::Fill2(rand()%1000000, 6);

    $res = $MAIL->Send($email, 'Auth code from 62che.com', "인증번호: {$code}");

    if($res['result'] != 0) {
        $response['result'] = 'FAIL';
        $response['msg'] = '이메일전송이 실패: '.$res['msg'];
    } else {
        getRootDB()->insertUpdate('EMAIL', [
            'EMAIL'   =>  $email,
            'CODE'    =>  $code,
            'VERIFIED'=>  0,
            'REG_DATE'=> _Time::DatetimeNow()
        ],[
            'CODE'    =>  $code,
            'VERIFIED'=>  0,
            'REG_DATE'=> _Time::DatetimeNow()
        ]);

        $response['result'] = 'SUCCESS';
        $response['msg'] = $email.'로 인증번호가 전송되었습니다. 이메일을 확인하세요.';
    }
}

//sleep(1);
echo json_encode($response);


