<?php
namespace sammo;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();



$session = Session::requireGameLogin([])->setReadOnly();

$generalID = $session->generalID;

$turnAmount = Util::getPost('amount', 'int');
$isRepeat = Util::getPost('is_repeat', 'bool', false);

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

if($isRepeat){
    repeatGeneralCommand($generalID, $turnAmount);
}
else{
    pushGeneralCommand($generalID, $turnAmount);
}

Json::die([
    'result'=>true,
    'reason'=>'success',
]);