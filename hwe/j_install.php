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

Json::die(ResetHelper::doReset(
    $turnterm,
    $sync,
    $scenario,
    $fiction,
    $extend,
    $npcmode,
    $show_img_level,
    $tournament_trig
));