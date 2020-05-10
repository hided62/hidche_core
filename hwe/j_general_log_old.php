<?php
namespace sammo;

include('lib.php');
include('func.php');

$session = Session::requireGameLogin([]);
$userID = Session::getUserID();

$generalID = Session::getGeneralID();

$targetID = Util::getPost('general_id', 'int', $generalID);
$reqTo = Util::getPost('to', 'int', PHP_INT_MAX);
$reqType = Util::getPost('type', 'string');

if(!in_array($reqType, [
    'generalAction',
    'battleResult',
    'battleDetail'
])){
    return Json::die([
        'result'=>false,
        'reason'=>'요청 타입이 올바르지 않습니다.'
    ]);
}

if($generalID <= 0 || $reqTo <= 0){
    return Json::die([
        'result'=>false,
        'reason'=>'요청 대상이 올바르지 않습니다.'
    ]);
}

$db = DB::db();

$me = $db->queryFirstRow('SELECT no,nation,officer_level,con,turntime,belong,permission,penalty from general where owner=%i', $userID);
$nationID = $me['nation'];


$con = checkLimit($me['con']);
if ($con >= 2) {
    Json::die([
        'result'=>false,
        'reason'=>'접속 제한입니다.'
    ]);
}

if($generalID !== $targetID){
    [$testGeneralNationID, $testGeneralNPCType] = $db->queryFirstList('SELECT nation,npc FROM general WHERE no = %i', $targetID);

    $permission = checkSecretPermission($me);
    if($permission < 0){
        Json::die([
            'result'=>false,
            'reason'=>'국가에 소속되어있지 않습니다.'
        ]);
    }
    if ($permission < 1 && $testGeneralNPCType < 2) {
        Json::die([
            'result'=>false,
            'reason'=>'권한이 부족합니다. 수뇌부가 아니거나 사관년도가 부족합니다.'
        ]);
    }
    
    if($testGeneralNationID !== $nationID){
        Json::die([
            'result'=>false,
            'reason'=>'동일한 국가의 장수가 아닙니다.'
        ]);
    }
}

if($reqType == 'generalAction'){
    $result = getGeneralActionLogMore($targetID, $reqTo, 30);
    Json::die([
        'result'=>true,
        'reason'=>'success',
        'log'=>array_map(function($data){return ConvertLog($data);}, $result)
    ]);
}
if($reqType == 'battleResult'){
    $result = getBattleResultMore($targetID, $reqTo, 30);
    Json::die([
        'result'=>true,
        'reason'=>'success',
        'log'=>array_map(function($data){return ConvertLog($data);}, $result)
    ]);
}
if($reqType == 'battleDetail'){
    $result = getBattleDetailLogMore($targetID, $reqTo, 30);
    Json::die([
        'result'=>true,
        'reason'=>'success',
        'log'=>array_map(function($data){return ConvertLog($data);}, $result)
    ]);
}
Json::die([
    'result'=>false,
    'reason'=>'Invalid'.$reqType,
]);