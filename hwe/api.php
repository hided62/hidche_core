<?php

namespace sammo;

include "lib.php";
include "func.php";

$eParams = $_GET;
if(key_exists('path', $eParams)){
    unset($eParams['path']);
}

APIHelper::launch(dirname(__FILE__), $_GET['path']??'', $eParams, true);