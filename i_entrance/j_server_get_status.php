<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');

// 외부 파라미터
$response['server'] = [];

foreach(getServerConfigList() as $serverInfo){
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
returnJson($response);


