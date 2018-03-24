<?php
namespace sammo;

require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');

$session = Session::Instance();

if(!$session->isLoggedIn()) {
    Json::die([
        'result'=>false,
        'reason'=>'로그인되지 않았습니다.'
    ]);
}

// 외부 파라미터

$respone = [];
$db = getRootDB();
$picName = $db->queryFirstField('SELECT picture FROM `MEMBER` WHERE `NO` = %i', $session->userID);

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
    ], 'NO=%i', $session->userID);
    
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

Json::die($response);