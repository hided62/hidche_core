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

$turnterm  = Util::toInt(Util::array_get($_POST['turnterm']));
$sync = Util::toInt(Util::array_get($_POST['sync']));
$scenario = Util::toInt(Util::array_get($_POST['scenario']));
$fiction = Util::toInt(Util::array_get($_POST['fiction']));
$extend = Util::toInt(Util::array_get($_POST['extend']));
$npcmode = Util::toInt(Util::array_get($_POST['npcmode']));
$show_img_level = Util::toInt(Util::array_get($_POST['show_img_level']));



if($turnterm===null || $sync===null || $scenario===null || $fiction===null || $extend===null || $npcmode===null || $show_img_level===null){
    Json::die([
        'result'=>false,
        'reason'=>'입력 값이 올바르지 않습니다'
    ]);
}


if(120 % $turnterm != 0){
    Json::die([
        'result'=>false,
        'turnterm은 120의 약수여야 합니다.'
    ]);
}

$mysqli_obj = DB::db()->get();

if($mysqli_obj->multi_query(file_get_contents(__dir__.'/sql/reset.sql'))){
    do{
        if(!$mysqli_obj->store_result()){
            break;
        }
    } while($mysqli_obj->next_result());
}

if($mysqli_obj->multi_query(file_get_contents(__dir__.'/sql/schema.sql'))){
    do{
        if(!$mysqli_obj->store_result()){
            break;
        }
    } while($mysqli_obj->next_result());
}

//TODO:script
$env = [
    
];

Json::die([
    'result'=>false,
    'reason'=>'NYI'
]);