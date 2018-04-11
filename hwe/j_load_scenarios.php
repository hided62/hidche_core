<?php
namespace sammo;

require "lib.php";
require "func.php";
$session = Session::requireLogin([])->setReadOnly();
if($session->userGrade < 5){
    Json::die([
        'result'=>false,
        'reason'=>'관리자가 아닙니다.'
    ]);
}


$scenarioIdx = Util::getReq('scenarioIdx', 'int');

if ($scenarioIdx !== null) {
    //TODO: preview 지도 출력
    Json::die([
        'result'=>false,
        'reason'=>'NYI'
    ]);
}


$scenarios = [];
foreach(Scenario::getAllScenarios() as $scenario){
    $scenarios[$scenario->getScenarioIdx()] = $scenario->getScenarioBrief();
}

Json::die([
    'result'=>true,
    'scenario'=>$scenarios
]);