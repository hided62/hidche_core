<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._JSON.php');
require_once(ROOT.'/f_func/class._String.php');
require_once(ROOT.'/f_func/class._Validation.php');
require_once(ROOT.'/f_config/DB.php');

// 외부 파라미터
// $_POST['type'] : 0: ID체크, 1: 주민번호 체크, 2: 이메일 체크
// $_POST['id'] : ID
// $_POST['pid1'] : 주민번호
// $_POST['pid2'] : 주민번호
// $_POST['email'] : 이메일
$type = $_POST['type'];
$id = $_POST['id'];
$pid1 = $_POST['pid1'];
$pid2 = $_POST['pid2'];
$pid = $_POST['pid1'].'-'.substr($_POST['pid2'],0,1).'-'.md5($_POST['pid2']);
$email = $_POST['email'];


$response['type'] = $type;
$db = getRootDB();

if($type == 0) {
    $err = _Validation::CheckID($id);
    if($err == 2) {
        $response['result'] = 'FAIL';
        $response['msg'] = '영어 소문자와 숫자만 입력 가능합니다!';
    } elseif($err == 1) {
        $response['result'] = 'FAIL';
        $response['msg'] = '4~12글자로 입력해주세요.';
    } elseif($err == 0) {
        $count = $db->queryFirstField('SELECT count(id) FROM MEMBER WHERE ID=%s', $id);
        if($count == 1) {
            $response['result'] = 'SUCCESS';
            $response['msg'] = '존재하는 아이디입니다. ^^';
        } else {
            $response['result'] = 'FAIL';
            $response['msg'] = '없는 아이디입니다! 운영자에게 문의해보세요!';
        }
    }
} elseif($type == 1) {
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
        $count = $db->queryFirstField('SELECT count(`NO`) FROM MEMBER WHERE ID = %s AND PID = %s', $id, $pid);
        if($count == 1) {
            $response['result'] = 'SUCCESS';
            $response['msg'] = '맞는 주민번호입니다. ^^';
        } else {
            $response['result'] = 'FAIL';
            $response['msg'] = '아이디에 맞지 않는 주민번호입니다! 운영자에게 문의해보세요!';
        }
    }
} elseif($type == 2) {
    $err = _Validation::CheckEmail($email);
    if($err == 1) {
        $response['result'] = 'FAIL';
        $response['msg'] = '이메일이 올바르지 않습니다!';
    } elseif($err == 0) {
        $count = $db->queryFirstField('SELECT COUNT(`NO`) FROM MEMBER WHERE ID = %s AND EMAIL = %s', $id, $email);
        if($count == 1) {
            $response['result'] = 'SUCCESS';
            $response['msg'] = '맞는 이메일입니다. ^^';
        } else {
            $response['result'] = 'FAIL';
            $response['msg'] = '아이디에 맞지 않는 이메일입니다! 운영자에게 문의해보세요!';
        }
    }
}

sleep(1);
echo json_encode($response);


