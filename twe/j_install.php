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

$db->insert('plock', [
    'plock'=>0
]);

/*
$db->insert('game', [

]);
*/
//TODO:script
$env = [
    
];

Json::die([
    'result'=>false,
    'reason'=>'NYI'
]);