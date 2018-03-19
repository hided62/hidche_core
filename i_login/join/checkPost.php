<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._String.php');
require_once(ROOT.'/f_func/class._Validation.php');
require_once(ROOT.'/f_config/DB.php');

// 외부 파라미터
// $_POST['type'] : 0: ID체크, 1: 주민번호 체크, 2: 닉네임 체크, 3: 이메일 체크
// $_POST['id'] : ID
// $_POST['pid1'] : 생년월일
// $_POST['pid2'] : 성별
// $_POST['name'] : 닉네임
// $_POST['email'] : 이메일
$type = $_POST['type'];
$id = $_POST['id'];
$pid1 = $_POST['pid1'];
$pid2 = $_POST['pid2'];
$pid = $_POST['pid1'].'-'.substr($_POST['pid2'],0,1).'-'.md5($_POST['pid2']);
$name = $_POST['name'];
$email = $_POST['email'];

$name = _String::NoSpecialCharacter($name);

$response['type'] = $type;
$db = getRootDB();

$system = $db->queryFirstRow('SELECT `REG` FROM `SYSTEM` WHERE `NO`=1');

if($system['REG'] != 'Y') {
    $response['result'] = 'FAIL';
    $response['msg'] = '현재는 가입이 금지되어있습니다!';
} else {
    if($type == 0) {
        $err = _Validation::CheckID($id);
        if($err == 2) {
            $response['result'] = 'FAIL';
            $response['msg'] = '영어 소문자와 숫자만 입력 가능합니다!';
        } elseif($err == 1) {
            $response['result'] = 'FAIL';
            $response['msg'] = '4~12글자로 입력해주세요.';
        } elseif($err == 0) {
            $count = $db->queryFirstField('SELECT COUNT(`ID`) FROM MEMBER WHERE ID = %s', $id);
            if($count == 0) {
                $response['result'] = 'SUCCESS';
                $response['msg'] = '사용할 수 있는 아이디입니다. ^^';
            } else {
                $response['result'] = 'FAIL';
                $response['msg'] = '이미 가입된 아이디입니다! 로그인을 시도해보세요!';
            }
        }
    } elseif($type == 1) {
        $err = _Validation::CheckBirth($pid1, $pid2);
//        $err = _Validation::CheckPID($pid1, $pid2);
        if($err == 3) {
            $response['result'] = 'FAIL';
            $response['msg'] = '잘못된 생년월일 입니다!';
        } elseif($err == 2) {
            $response['result'] = 'FAIL';
            $response['msg'] = '숫자가 아닙니다!';
        } elseif($err == 1) {
            $response['result'] = 'FAIL';
            $response['msg'] = '입력이 충분치 않습니다!';
        } elseif($err == 0) {
            $response['result'] = 'SUCCESS';
            $response['msg'] = '사용할 수 있는 생년월일입니다. ^^';
        }
    } elseif($type == 2) {
        $err = _Validation::CheckName($name);
        if($err >= 1) {
            $response['result'] = 'FAIL';
            $response['msg'] = '닉네임이 올바르지 않습니다!'." ($err)";
        } elseif($err == 0) {
            $count = $db->queryFirstField('SELECT COUNT(`NAME`) FROM MEMBER WHERE `NAME` = %s', $name);
            if($count == 0) {
                $response['result'] = 'SUCCESS';
                $response['name'] = $name;
                $response['msg'] = $name.' 은(는) 사용할 수 있는 닉네임입니다. ^^';
            } else {
                $response['result'] = 'FAIL';
                $response['msg'] = $name.' 은(는) 이미 존재하는 닉네임입니다! 운영자에게 문의해보세요!';
            }
        }
    } elseif($type == 3) {
        $err = _Validation::CheckEmail($email);
        if($err == 1) {
            $response['result'] = 'FAIL';
            $response['msg'] = '이메일이 올바르지 않습니다!';
        } elseif($err == 0) {
            $count = $db->queryFirstField('SELECT COUNT(`NO`) FROM MEMBER WHERE EMAIL = %s', $email);
            if($count == 0) {
                $response['result'] = 'SUCCESS';
                $response['msg'] = $email.' 은(는) 사용할 수 있는 이메일입니다. ^^';
            } else {
                $response['result'] = 'FAIL';
                $response['msg'] = $email.' 은(는) 이미 존재하는 이메일입니다! 운영자에게 문의해보세요!';
            }
        }
    }
}

echo json_encode($response);

?>