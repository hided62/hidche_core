<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_func/class._Session.php');

$SESSION = new _Session();

if(!$SESSION->isLoggedIn()) {
    returnJson([
        'result'=>false,
        'reason'=>'로그인되지 않았습니다.'
    ]);
}

// 외부 파라미터

$respone = [];
$db = getRootDB();
$picName = $db->queryFirstField('SELECT picture FROM `MEMBER` WHERE `NO` = %i', $SESSION->NoMember());

if($picName && strlen($picName) > 11){
    $dt = substr($picName, -8);
    $picName = substr($picName, 0, -10);
}
else{
    $dt = '00000000';
}

$dest = ROOT.'/d_pic/'.$picName;

$rf = date('Ymd');

$response['result'] = false;
$response['reason'] = '요청이 올바르지 않습니다!';

if($dt == $rf) {
    //갱신날짜 검사
    $response['reason'] = '1일 1회 변경 가능합니다!';
    $response['result'] = false;
} else {
    $db->update('MEMBER', [
        'PICTURE'=>'default.jpg',
        'IMGSVR'=>0,
    ], 'NO=%i', $SESSION->NoMember());
    
    //TODO: 각 세부 서버가 '열린 경우' 이미지를 갱신하도록 처리
    //Token을 받아 처리하는 형식이면 가능할듯.
    /*
    for($i=0; $i < ; $i++) {
                Update('general', "PICTURE='default.jpg', IMGSVR=0", "NPC=0 AND USER_ID='{$member['ID']}'");
    }
    */

    $servers = [];

    foreach(getServerConfigList() as $key=>$server){
        $setting = $server[2];
        if($setting->isExists()){
            $servers[] = $key;
        }
    }

    $response['servers'] = $servers;
    $response['reason'] = '제거에 성공했습니다!';
    $response['result'] = true;
}

returnJson($response);