<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::getInstance()->setReadOnly();

$db = DB::db();

$updated = false;
$locked = false;
$lastExecuted = TurnExecutionHelper::executeAllCommand($updated, $locked);
Json::die([
    'result' => true,
    'updated' => $updated,
    'locked' => $locked,
    'lastExecuted' => $lastExecuted,
]);