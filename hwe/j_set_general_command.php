<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireGameLogin([])->setReadOnly();

$generalID = $session->generalID;

$action = Util::getReq('action', 'string');
$arg = Json::decode(Util::getReq('arg', 'string'));
$turnList = Util::getReq('turnList', 'array_int');

if(!is_array($turnList) || !$turnList){
    Json::die([
        'result'=>false,
        'reason'=>'턴이 입력되지 않았습니다.',
        'test'=>'post'
    ]);
}

if(!$action){
    Json::die([
        'result'=>false,
        'reason'=>'action이 입력되지 않았습니다.',
        'test'=>'post'
    ]);
}

if(!in_array($action, Util::array_flatten(GameConst::$availableGeneralCommand))){
    Json::die([
        'result'=>false,
        'reason'=>'사용할 수 없는 커맨드입니다.',
        'test'=>'post'
    ]);
}

if($arg === null){
    $arg = [];
}

if(!is_array($arg)){
    Json::die([
        'result'=>false,
        'reason'=>'올바른 arg 형태가 아닙니다.',
        'test'=>'post'
    ]);
}

Json::die(setGeneralCommand($generalID, $turnList, $action, $arg));