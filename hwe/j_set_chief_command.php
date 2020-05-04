<?php
namespace sammo;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();

$session = Session::requireGameLogin([])->setReadOnly();

$generalID = $session->generalID;


$action = Util::getPost('action', 'string');
$arg = Json::decode(Util::getPost('arg', 'string'));
$turnList = Util::getPost('turnList', 'array_int');

if(!is_array($turnList) || !$turnList){
    Json::die([
        'result'=>false,
        'reason'=>'턴이 입력되지 않았습니다.',
        'test'=>'post',
    ]);
}

if(!$action){
    Json::die([
        'result'=>false,
        'reason'=>'action이 입력되지 않았습니다.',
        'test'=>'post'
    ]);
}

if(!in_array($action, Util::array_flatten(GameConst::$availableChiefCommand))){
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