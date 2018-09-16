<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireGameLogin([])->setReadOnly();

$db = DB::db();

$commandList = [];

$generalID = $session->generalID;

$rawTurn = $db->queryAllLists('SELECT turn_idx, action, arg FROM general_turn WHERE general_id = %i ORDER BY turn_idx ASC');
foreach($rawTurn as [$turn_idx, $action, $arg]){
    $commandList[$turn_idx] = [
        'action'=>$action,
        'arg'=>Json::decode($arg)
    ];
}

Json::die($commandList);