<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireGameLogin([])->setReadOnly();

$generalID = $session->generalID;


$action = Util::getReq('action', 'string');
$arg = Json::decode(Util::getReq('arg', 'string'));
$turnList = Json::decode(Util::getReq('turnList', 'string'));

if(!is_array($turnList) || !$turnList){
    Json::die([
        'result'=>false,
        'reason'=>'턴이 입력되지 않았습니다.'
    ]);
}

if(!$action){
    Json::die([
        'result'=>false,
        'reason'=>'action이 입력되지 않았습니다.'
    ]);
}

if($arg === null || !is_array($arg)){
    Json::die([
        'result'=>false,
        'reason'=>'올바른 arg 형태가 아닙니다.'
    ]);
}

$result = setNationCommand($generalID, $turnList, $action, $arg);
if(!key_exists('result', $result)){
    $result['result'] = false;
}
if(!key_exists('arg_test', $result)){
    $result['arg_test'] = false;
}
if(!key_exists('reason', $result)){
    throw new MustNotBeReachedException('reason이 왜 없어?');
}
Json::die($result);