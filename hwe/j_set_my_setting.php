<?php
namespace sammo;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();

$session = Session::requireGameLogin([])->setReadOnly();
$userID = Session::getUserID();
$generalID = $session->generalID;

increaseRefresh("내정보 수정", 0);

$action = Util::getPost('action');
$tnmt = Util::getPost('tnmt', 'int', 1);
$defence_train = Util::getPost('defence_train', 'int', 80);
$use_treatment = Util::getPost('use_treatment', 'int', 10);
$use_auto_nation_turn = Util::getPost('use_auto_nation_turn', 'int', 1);
//$detachNPC = Util::getPost('detachNPC', 'bool');
$detachNPC = false;

if ($defence_train <= 40) {
    $defence_train = 40;
}

if($defence_train <= 90){
    $defence_train = Util::round($defence_train, -1);
}
else{
    $defence_train = 999;
}

if($tnmt < 0 || $tnmt > 1){
    $tnmt = 1;
}

$db = DB::db();
$me = General::createObjFromDB($generalID);


if($defence_train !== $me->getVar('defence_train')){
    if($defence_train == 999){
        $me->increaseVar('myset', -1);
        $me->setVar('defence_train', $defence_train);
        $affectedTrain = -3;
        $affectedAtmos = -6;
        $affectedTrain = $me->onCalcDomestic('changeDefenceTrain', "train{$defence_train}", $affectedTrain);
        $affectedAtmos = $me->onCalcDomestic('changeDefenceTrain', "atmos{$defence_atmos}", $affectedAtmos);
        $me->increaseVarWithLimit('train', $affectedTrain, 20, GameConst::$maxTrainByWar);
        $me->increaseVarWithLimit('atmos', $affectedAtmos, 20, GameConst::$maxAtmosByWar);
    }
    else{
        $me->increaseVar('myset', -1);
        $me->setVar('defence_train', $defence_train);
    }
}

$me->setAuxVar('use_treatment', Util::valueFit($use_treatment, 10, 100));
$me->setAuxVar('use_auto_nation_turn', $use_auto_nation_turn);
$me->setVar('tnmt', $tnmt);

if($me->getNPCType() == 1 && $detachNPC){
    $turnterm = $gameStor->turnterm;

    if($turnterm < 10){
        $targetKillTurn = 30 / $turnterm;
    }
    else{
        $targetKillTurn = 60 / $turnterm;
    }
    $me->setVar('killturn', $targetKillTurn);
}

$me->applyDB($db);

Json::die([
    'result'=>true,
    'reason'=>'success'
]);