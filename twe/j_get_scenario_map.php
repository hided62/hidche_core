<?php

require(__DIR__.'/../f_func/func.php');
require('func_install.php');

$session = Session::requireLogin();

if($session->userGrade < 5){
    Json::die([
        'result'=>false,
        'reason'=>'충분한 권한을 가지고 있지 않습니다.'
    ]);
}

$scenarioIdx = toInt(util::array_get($_GET['scenarioIdx']));

if($scenarioIdx === null){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 scenarioIdx'
    ]);
}

