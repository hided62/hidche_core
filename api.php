<?php

namespace sammo;

require(__DIR__ . '/vendor/autoload.php');


if (!class_exists('\\sammo\\RootDB')) {
    Json::dieWithReason('No DB');
}
$eParams = $_GET;
if(key_exists('path', $eParams)){
    unset($eParams['path']);
}

APIHelper::launch(dirname(__FILE__), $_GET['path']??'', $eParams, true);