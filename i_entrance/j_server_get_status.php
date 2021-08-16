<?php
namespace sammo;

require(__DIR__.'/../vendor/autoload.php');

// 외부 파라미터
$response['server'] = [];

foreach(ServConfig::getServerList() as $setting){

    $serverObj = [
        'color'=>$setting->getColor(),
        'korName'=>$setting->getKorName(),
        'name'=>$setting->getShortName(),
        'exists'=>$setting->isExists(),
        'enable'=>$setting->isRunning()
    ];

    $response['server'][] = $serverObj;
}

$response['result'] = true;
$response['reason'] = 'success';
Json::die($response);


