<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::getInstance()->setReadOnly();

$db = DB::db();

$updated = false;
$locked = false;
$lastExecuted = TurnExecutionHelper::executeAllCommand($updated, $locked);
$clock = GameClock::fromStorage(KVStorage::getStorage($db, 'game_env'));
Json::die([
    'result' => true,
    'updated' => $updated,
    'locked' => $locked,
    'lastExecutedTick' => $lastExecuted,
    'lastExecuted' => $clock->formatTick($lastExecuted, true),
]);
