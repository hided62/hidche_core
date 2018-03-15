<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

// 외부 파라미터

$db = getRootDB();
$member = $db->queryFirstRow('SELECT `ID`, `NAME`, `GRADE`, `PICTURE` FROM `MEMBER` WHERE `NO` = %i', $SESSION->NoMember());

$response['id'] = $member['ID'];
$response['name'] = $member['NAME'];
if($member['GRADE'] == 6) {
    $response['grade'] = '운영자';
} elseif($member['GRADE'] == 5) {
    $response['grade'] = '부운영자';
} elseif($member['GRADE'] == 4) {
    $response['grade'] = '특별회원';
} elseif($member['GRADE'] == 1) {
    $response['grade'] = '일반회원';
} elseif($member['GRADE'] == 0) {
    $response['grade'] = '블럭회원';
}
if($member['PICTURE'] == '') {
    $response['picture0'] = IMAGE.W.'default.jpg';
    $response['picture1'] = IMAGE.W.'default.jpg';
} else {
    $response['picture0'] = IMAGE.W.$member['PICTURE'];
    $response['picture1'] = '../d_pic/'.$member['PICTURE'];
}

$response['result'] = 'SUCCESS';

sleep(1);
echo json_encode($response);


