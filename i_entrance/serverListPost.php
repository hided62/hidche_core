<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

// 외부 파라미터
$response['serverCount'] = count($serverList);
$response['servers'] = '';
$response['server'] = [];

foreach($serverList as $serverInfo){
    list($serverKorName, $serverColor, $setting) = $serverInfo;

    $serverObj = [
        'color'=>$serverColor,
        'korName'=>$serverKorName,
        'name'=>$setting->getShortName(),
        'enable'=>$setting->isExist()
    ];

    $response['server'][] = $serverObj;
}

$response['result'] = 'SUCCESS';
returnJson($response);


