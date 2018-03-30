<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::Instance()->setReadOnly();
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
    'show_img_level'
]);
if(!$v->validate()){
    $errors = array_values((array)$v->errors());
    $errors = array_map(function($value){return join(', ', $value);}, $errors);
    $errors = join(', ', $errors);
    Json::die([
        'result'=>false,
        'reason'=>$errors
    ]);
}

$turnterm  = (int)$_POST['turnterm'];
$sync = (int)$_POST['sync'];
$scenario = (int)$_POST['scenario'];
$fiction = (int)$_POST['fiction'];
$extend = (int)$_POST['extend'];
$npcmode = (int)$_POST['npcmode'];
$show_img_level = (int)$_POST['show_img_level'];

if(120 % $turnterm != 0){
    Json::die([
        'result'=>false,
        'turnterm은 120의 약수여야 합니다.'
    ]);
}

$db = DB::db();
$mysqli_obj = $db->get();


$scenarioObj = new Scenario($scenario);

if($mysqli_obj->multi_query(file_get_contents(__dir__.'/sql/reset.sql'))){
    while (@$mysqli_obj->next_result()) {;}
}

if($mysqli_obj->multi_query(file_get_contents(__dir__.'/sql/schema.sql'))){
    while (@$mysqli_obj->next_result()) {;}
}

CityConst::build();

$db->insert('plock', [
    'plock'=>0
]);


$turntime = date('Y-m-d H:i:s');
$time = substr($turntime, 11, 2);
if($sync == 0) {
    // 현재 시간을 1월로 맞춤
    $starttime = cutTurn($turntime, $turnterm);
    $month = 1;
    $year = $startyear;
} else {
    // 현재 시간과 동기화
    list($starttime, $yearPulled, $month) = cutDay($turntime, $turnterm);
    if($yearPulled){
        $year = $startyear-1;
    }
    else{
        $year = $startyear;
    }
}

$killturn = 4800 / $turnterm;
if($npcmode == 1) { $killturn = floor($killturn / 3); }

$env = [
    'year'=> $year,
    'month'=> $month,
    'msg'=>'공지사항',//TODO:공지사항
    'maxgeneral'=>500,
    'normgeneral'=>300,
    'maxnation'=>55,
    'conlimit'=>300,
    'gold_rate'=>100,
    'rice_rate'=>100,
    'turntime'=>$turntime,
    'starttime'=>$starttime,
    'turnterm'=>$turnterm,
    'killturn'=>$killturn,
    'genius'=>5,
    'startyear'=>$startyear,
    'scenario'=>$scenario,
    'show_img_level'=>$show_img_level,
    'npcmode'=>$npcmode,
    'extend'=>$extend,
    'fiction'=>$fiction
];

$db->insert('game', $env);

$scenario->build($env);

Json::die([
    'result'=>true
]);