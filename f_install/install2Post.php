<?php
require_once('_common.php');

// id, pw
$id = $_POST['id'];
$pw = $_POST['pw'];
$pw = md5($pw.$pw);

require_once(ROOT.'/f_func/class._Time.php');
require_once(ROOT.'/f_config/DB.php');

// 시스템정보 1개 등록
$db->insert('SYSTEM', array(
    'REG'     => 'N',
    'LOGIN'    => 'N',
    'CRT_DATE' => _Time::DatetimeNow(),
    'MDF_DATE' => _Time::DatetimeNow()
));

// 운영자 1명 등록
$db->insert('MEMBER', array(
    'ID'      => $id,
    'PW'      => $pw,
    'NAME'    => '운영자',
    'GRADE'   => 6,
    'REG_DATE' => _Time::DatetimeNow()
));


// 부운영자 1명 등록
$db->insert('MEMBER', array(
    'ID'      => 'viceadmin',
    'PW'      => 'aZ',
    'NAME'    => '부운영자',
    'GRADE'   => 5,
    'REG_DATE' => _Time::DatetimeNow()
));

?>

설치가 완료되었습니다.
