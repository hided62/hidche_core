<?php
// id, pw
$id = $_POST['id'];
$pw = $_POST['pw'];
$pw = md5($pw.$pw);

require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._Time.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);

// 시스템정보 1개 등록
$DB->InsertArray('SYSTEM', array(
    'REG'     => 'N',
    'LOGIN'    => 'N',
    'CRT_DATE' => _Time::DatetimeNow(),
    'MDF_DATE' => _Time::DatetimeNow()
));

// 운영자 1명 등록
$DB->InsertArray('MEMBER', array(
    'ID'      => $id,
    'PW'      => $pw,
    'PID'     => '-',
    'NAME'    => '운영자',
    'EMAIL'   => 'nomail@nomail.com',
    'IP'      => $_SERVER['REMOTE_ADDR'],
    'GRADE'   => 6,
    'REG_DATE' => _Time::DatetimeNow()
));


// 부운영자 1명 등록
$DB->InsertArray('MEMBER', array(
    'ID'      => 'viceadmin',
    'PW'      => md5('12qw!@QWQPQP%12qw!@QWQPQP%'),
    'PID'     => '-',
    'NAME'    => '부운영자',
    'EMAIL'   => 'nomail@nomail.com',
    'IP'      => $_SERVER['REMOTE_ADDR'],
    'GRADE'   => 5,
    'REG_DATE' => _Time::DatetimeNow()
));

?>

설치가 완료되었습니다.
