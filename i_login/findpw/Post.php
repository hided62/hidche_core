<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._Time.php');
require_once(ROOT.'/f_func/class._String.php');
require_once(ROOT.'/f_func/class._Validation.php');
require_once(ROOT.'/f_config/DB.php');

// 외부 파라미터
// $_POST['id'] : ID
// $_POST['pid1'] : 주민번호
// $_POST['pid2'] : 주민번호
// $_POST['email'] : 이메일
$id = $_POST['id'];
$pid1 = $_POST['pid1'];
$pid2 = $_POST['pid2'];
$pid = $_POST['pid1'].'-'.substr($_POST['pid2'],0,1).'-'.md5($_POST['pid2']);
$email = $_POST['email'];

$db = getRootDB();

$response['result'] = 'FAIL';
$err = _Validation::CheckID($id);
if($err == 2) {
    $response['result'] = 'FAIL';
    $response['msg'] = '영어 소문자와 숫자만 입력 가능합니다!';
} elseif($err == 1) {
    $response['result'] = 'FAIL';
    $response['msg'] = '4~12글자로 입력해주세요.';
} elseif($err == 0) {
    $count = $db->queryFirstField('SELECT COUNT(`ID`) FROM MEMBER WHERE `ID` = %s', $id);
    if($count == 1) {
        $response['result'] = 'SUCCESS';
    } else {
        $response['result'] = 'FAIL';
        $response['msg'] = '없는 아이디입니다! 운영자에게 문의해보세요!';
    }
}

if($response['result'] != 'SUCCESS') {
    echo json_encode($response);
    exit(0);
}

$response['result'] = 'FAIL';
$err = _Validation::CheckPID($pid1, $pid2);
if($err == 3) {
    $response['result'] = 'FAIL';
    $response['msg'] = '잘못된 주민번호 입니다!';
} elseif($err == 2) {
    $response['result'] = 'FAIL';
    $response['msg'] = '숫자가 아닙니다!';
} elseif($err == 1) {
    $response['result'] = 'FAIL';
    $response['msg'] = '입력이 충분치 않습니다!';
} elseif($err == 0) {
    $response['result'] = 'SUCCESS';
}

if($response['result'] != 'SUCCESS') {
    echo json_encode($response);
    exit(0);
}

$response['result'] = 'FAIL';
$err = _Validation::CheckEmail($email);
if($err == 1) {
    $response['result'] = 'FAIL';
    $response['msg'] = '이메일이 올바르지 않습니다!';
} elseif($err == 0) {
    $count = $db->queryFirstField('SELECT COUNT(`NO`) FROM MEMBER WHERE ID = %s AND EMAIL = %s', $id, $email);
    if($count == 1) {
        $response['result'] = 'SUCCESS';
    } else {
        $response['result'] = 'FAIL';
        $response['msg'] = '아이디에 맞지 않는 이메일입니다! 운영자에게 문의해보세요!';
    }
}

if($response['result'] != 'SUCCESS') {
    echo json_encode($response);
    exit(0);
}

$response['result'] = 'FAIL';
$verified = $db->queryFirstField('SELECT VERIFIED FROM EMAIL WHERE EMAIL = %s', $email);
if($verified === null) {
    $response['result'] = 'FAIL';
    $response['msg'] = $email.' 에 대한 인증정보가 없습니다! 운영자에게 문의해보세요!';
} else {
    if($verified) {
        $response['result'] = 'SUCCESS';
    } else {
        $response['result'] = 'FAIL';
        $response['msg'] = $email.' 에 대한 인증이 되지 않았습니다! 운영자에게 문의해보세요!';
    }
}

if($response['result'] != 'SUCCESS') {
    echo json_encode($response);
    exit(0);
}

// 비밀번호 변경
$pw = md5('11111111');
$db->update('MEMBER', ['PW'=>$pw], 'ID=%s', $id);

$response['result'] = 'SUCCESS';
$response['msg'] = "정상적으로 비번이 변경되었습니다. ID: {$id}";

echo json_encode($response);


