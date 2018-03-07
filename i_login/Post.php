<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._JSON.php');
require_once(ROOT.'/f_func/class._Session.php');
require_once(ROOT.'/f_func/class._String.php');
require_once(ROOT.'/f_config/DB.php');

use utilphp\util as util;

// 외부 파라미터
// $_POST['id'] : ID
// $_POST['pw'] : PW
// $_POST['conmsg'] : 접속장소
$id = $_POST['id'];
$pw = $_POST['pw'];
$conmsg = $_POST['conmsg'];


$id = _String::NoSpecialCharacter($id);
$pw = substr($pw, 0, 32);

$response['result'] = 'FAIL';

$SESSION = new _Session();
$db = getRootDB();
$member = $db->queryFirstRow('SELECT `NO`, `GRADE`, `QUIT` FROM `MEMBER` WHERE `ID` = %s AND `PW` = %s', $id, $pw);
if($member) {
    if($member['QUIT'] != 'Y') {
        $system = $db->queryFirstRow('SELECT `LOGIN` FROM `SYSTEM` WHERE `NO` = 1');

        if($system['LOGIN'] == 'Y' || $member['GRADE'] >= 5) {
            $SESSION->Login($member['NO'], $id, $member['GRADE']);
            $db->update('MEMBER', [
                'CONMSG'=>$conmsg,
                'IP'=>util::get_client_ip(true),
            ], 'NO=%i', $member['NO']);

            $_SESSION['conmsg'] = $conmsg;//XXX: conmsg를 처리할 더 적절한 장소는?

            $response['result'] = 'SUCCESS';
        } else {
            $response['result'] = 'FAIL';
            $response['msg'] = '로그인 실패! 현재는 로그인이 금지되어있습니다.';
        }
    } else {
        $response['result'] = 'FAIL';
        $response['msg'] = '탈퇴 신청중인 계정입니다!';
    }
} else {
    $response['result'] = 'FAIL';
    $response['msg'] = '로그인 실패! ID, PW를 확인해주세요.';
}

sleep(1);
echo json_encode($response);


