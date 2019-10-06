<?php
namespace sammo;

include "lib.php";
include "func.php";



$session = Session::requireGameLogin([])->setReadOnly();

$generalID = $session->generalID;

$turnAmount = Util::getReq('amount', 'int');

if($turnAmount == null){
    Json::die([
        'result'=>false,
        'reason'=>'턴이 입력되지 않았습니다.',
    ]);
}

if(abs($turnAmount) >= GameConst::$maxTurn){
    Json::die([
        'result'=>false,
        'reason'=>'턴 숫자가 올바르지 않습니다.',
    ]);
}

pushGeneralCommand($generalID, $turnAmount);
Json::die([
    'result'=>true,
    'reason'=>'success',
]);