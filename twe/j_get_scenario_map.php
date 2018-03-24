<?php
namespace sammo;


require(__DIR__.'/../f_func/config.php');
require('func_install.php');

$session = Session::requireLogin();

if($session->userGrade < 5){
    Json::die([
        'result'=>false,
        'reason'=>'충분한 권한을 가지고 있지 않습니다.'
    ]);
}

$scenarioIdx = Util::toInt(Util::array_get($_GET['scenarioIdx']));

if($scenarioIdx === null){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 scenarioIdx'
    ]);
}

