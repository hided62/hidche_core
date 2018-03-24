<?php
namespace sammo;

require_once('_common.php');

// 외부 파라미터
$response['server'] = [];

foreach(AppConf::getList() as $serverInfo){
    list($serverKorName, $serverColor, $setting) = $serverInfo;

    $serverObj = [
        'color'=>$serverColor,
        'korName'=>$serverKorName,
        'name'=>$setting->getShortName(),
        'enable'=>$setting->isExists()
    ];

    $response['server'][] = $serverObj;
}

$response['result'] = 'SUCCESS';
Json::die($response);


