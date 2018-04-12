<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

// 외부 파라미터
$response['server'] = [];

foreach(AppConf::getList() as $setting){

    $serverObj = [
        'color'=>$setting->getColor(),
        'korName'=>$setting->getKorName(),
        'name'=>$setting->getShortName(),
        'enable'=>$setting->isRunning()
    ];

    $response['server'][] = $serverObj;
}

$response['result'] = 'SUCCESS';
Json::die($response);


