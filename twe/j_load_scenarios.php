<?php
namespace sammo;

require "lib.php";

if(Session::getUserGrade() < 5){
    Json::die([
        'result'=>false,
        'reason'=>'관리자가 아닙니다.'
    ]);
}


$scenarioIdx = Util::toInt(Util::array_get($_GET['scenarioIdx']));

if ($scenarioIdx !== null) {
    //TODO: preview 지도 출력
    Json::die([
        'result'=>false,
        'reason'=>'NYI'
    ]);
}

Json::die([
    'result'=>false,
    'reason'=>'NYI'
]);