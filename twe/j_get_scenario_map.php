<?php

require(__DIR__.'/../f_func/func.php');
require(__DIR__.'/../f_config/SESSION.php');
require('func_install.php');

use utilphp\util as util;

if($SESSION->getGrade() < 5){
    returnJson([
        'result'=>false,
        'reason'=>'충분한 권한을 가지고 있지 않습니다.'
    ]);
}

$scenarioIdx = toInt(util::array_get($_GET['scenarioIdx']));

if($scenarioIdx === null){
    returnJson([
        'result'=>false,
        'reason'=>'올바르지 않은 scenarioIdx'
    ]);
}

