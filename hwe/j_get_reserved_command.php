<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireGameLogin([])->setReadOnly();

$db = DB::db();

$commandList = [];
$gameStor = KVStorage::getStorage($db, 'game_env');
$generalID = $session->generalID;

$rawTurn = $db->queryAllLists('SELECT turn_idx, action, arg, brief FROM general_turn WHERE general_id = %i ORDER BY turn_idx ASC', $generalID);
foreach($rawTurn as [$turn_idx, $action, $arg, $brief]){
    $commandList[$turn_idx] = [
        'action'=>$action,
        'brief'=>$brief,
        'arg'=>Json::decode($arg)
    ];
}

[$turnTerm, $year, $month, $lastExecute] = $gameStor->getValuesAsArray(['turnterm', 'year', 'month', 'turntime']);

$turnTime = $db->queryFirstField('SELECT turntime FROM general WHERE no=%i', $generalID);

if(cutTurn($turnTime, $turnTerm) > cutTurn($lastExecute, $turnTerm)){
    //이미 이번달에 실행된 턴이다.
    $month++;
    if($month >= 13){
        $month -= 12;
        $year += 1;
    }
}


Json::die([
    'result'=>true,
    'turnTime'=>$turnTime,
    'turnTerm'=>$turnTerm,
    'year'=>$year,
    'month'=>$month,
    'date'=>TimeUtil::now(true),
    'turn'=>$commandList
]);