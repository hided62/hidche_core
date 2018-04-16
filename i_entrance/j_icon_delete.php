<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

$session = Session::requireLogin([])->setReadOnly();
$userID = Session::getUserID();

// 외부 파라미터

$respone = [];
$db = RootDB::db();
$picName = $db->queryFirstField('SELECT picture FROM `member` WHERE `NO` = %i', $userID);

if($picName && strlen($picName) > 11){
    $dt = substr($picName, -8);
    $picName = substr($picName, 0, -10);
}
else{
    $dt = '00000000';
}

$dest = AppConf::getUserIconPathFS().'/'.$picName;

$rf = date('Ymd');

$response['result'] = false;
$response['reason'] = '요청이 올바르지 않습니다!';

if($dt == $rf) {
    //갱신날짜 검사
    $response['reason'] = '1일 1회 변경 가능합니다!';
    $response['result'] = false;
} else {
    $db->update('member', [
        'PICTURE'=>'default.jpg',
        'IMGSVR'=>0,
    ], 'NO=%i', $userID);
    
    $servers = [];

    foreach(AppConf::getList() as $key=>$setting){

        if($setting->isRunning()){
            $servers[] = $key;
        }
    }

    $response['servers'] = $servers;
    $response['reason'] = '제거에 성공했습니다!';
    $response['result'] = true;
}

Json::die($response);