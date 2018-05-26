<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin([])->setReadOnly();
if($session->userGrade < 5){
    Json::die([
        'result'=>false,
        'reason'=>'관리자 아님'
    ]);
}

$v = new Validator($_POST);
$v->rule('required', [
    'turnterm',
    'sync',
    'scenario',
    'fiction',
    'extend',
    'npcmode',
    'show_img_level'
])->rule('integer', [
    'turnterm',
    'sync',
    'scenario',
    'fiction',
    'extend',
    'npcmode',
    'show_img_level',
    'tournament_trig'
]);
if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>$v->errorStr()
    ]);
}

$turnterm  = (int)$_POST['turnterm'];
$sync = (int)$_POST['sync'];
$scenario = (int)$_POST['scenario'];
$fiction = (int)$_POST['fiction'];
$extend = (int)$_POST['extend'];
$npcmode = (int)$_POST['npcmode'];
$show_img_level = (int)$_POST['show_img_level'];
$tournament_trig = (int)$_POST['tournament_trig'];

$reserve_open = Util::getReq('reserve_open');
if($reserve_open){
    $reserve_open = new \DateTime($reserve_open);
    $db = DB::db();
    
    if (!$db->queryFirstField("SHOW TABLES LIKE 'storage'")) {
        $clearResult = ResetHelper::clearDB();
        if(!$clearResult['result']){
            Json::die($clearResult);
        }
    }

    if (!$db->queryFirstField("SHOW TABLES LIKE 'reserved_open'")) {
        Json::die([
            'result'=>false,
            'reason'=>'예약 테이블이 없음!'
        ]);
    }

    $scenarioObj = new Scenario($scenario, true);
    $db->delete('reserved_open', true);
    $db->insert('reserved_open', [
        'options'=>Json::encode([
            'turnterm'=>$turnterm,
            'sync'=>$sync,
            'scenario'=>$scenario,
            'scenarioName'=>$scenarioObj->getTitle(),
            'fiction'=>$fiction,
            'extend'=>$extend,
            'npcmode'=>$npcmode,
            'show_img_level'=>$show_img_level,
            'tournament_trig'=>$tournament_trig,
            'gameConf'=>$scenarioObj->getGameConf()
        ]),
        'date'=>$reserve_open->format('Y-m-d H:i:s')
    ]);
    Json::die([
        'result'=>true,
        'reason'=>'예약'
    ]);
}

Json::die(ResetHelper::buildScenario(
    $turnterm,
    $sync,
    $scenario,
    $fiction,
    $extend,
    $npcmode,
    $show_img_level,
    $tournament_trig
));