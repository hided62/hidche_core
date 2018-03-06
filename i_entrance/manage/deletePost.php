<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);
require_once(ROOT.W.F_CONFIG.W.SESSION.PHP);

// 외부 파라미터

$respone = [];
$db = getRootDB();
$member = $db->queryFirstRow('SELECT ID, PICTURE FROM `MEMBER` WHERE `NO` = %i', $SESSION->NoMember());



$picName = $member['PICTURE'];

if($picName && strlen($picName) > 11){
    $dt = substr($picName, -8);
    $picName = substr($picName, 0, -10);
}
else{
    $dt = '00000000';
}

$dest = ROOT.W.D."pic/{$picName}";

$rf = date('Ymd');

$response['result'] = 'FAIL';
$response['msg'] = '요청이 올바르지 않습니다!';

if($dt == $rf) {
    //갱신날짜 검사
    $response['msg'] = '1일 1회 변경 가능합니다!';
    $response['result'] = 'FAIL';
} else {
    $db->update('MEMBER', [
        'PICTURE'=>'',
        'IMGSVR'=>0,
    ], 'NO=%i', $SESSION->NoMember());
    
    //TODO: 각 세부 서버가 '열린 경우' 이미지를 갱신하도록 처리
    //Token을 받아 처리하는 형식이면 가능할듯.
    /*
    for($i=0; $i < ; $i++) {
                Update('general', "PICTURE='default.jpg', IMGSVR=0", "NPC=0 AND USER_ID='{$member['ID']}'");
    }
    */

    $response['msg'] = '제거에 성공했습니다!';
    $response['result'] = 'SUCCESS';
}

sleep(1);
echo json_encode($response);