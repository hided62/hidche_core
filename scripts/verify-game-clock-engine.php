#!/usr/bin/env php
<?php

declare(strict_types=1);

use sammo\DB;
use sammo\GameClock;
use sammo\KVStorage;
use sammo\TurnExecutionHelper;
use sammo\Util;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$_SERVER['REMOTE_ADDR'] ??= '127.0.0.1';
$_SERVER['REQUEST_URI'] ??= '/cli/verify-game-clock-engine';

require dirname(__DIR__) . '/hwe/lib.php';
require dirname(__DIR__) . '/hwe/func.php';

$options = getopt('', ['apply', 'engine-calls:']);
if (!isset($options['apply'])) {
    fwrite(STDERR, "Usage: php scripts/verify-game-clock-engine.php --apply [--engine-calls=N]\n");
    exit(2);
}
$engineCalls = filter_var($options['engine-calls'] ?? '1', FILTER_VALIDATE_INT);
if ($engineCalls === false || $engineCalls < 1 || $engineCalls > 1000) {
    throw new InvalidArgumentException('--engine-calls는 1..1000이어야 합니다.');
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$clock = GameClock::fromStorage($gameStor);
if ($clock->getMode() !== GameClock::MODE_MANUAL) {
    throw new RuntimeException('재현 검증은 manual clock에서만 실행할 수 있습니다.');
}

$fixedNowTick = $clock->nowTick();
$before = $gameStor->getValues(['year', 'month', 'turntime']);
$executedCount = 0;
for ($call = 0; $call < $engineCalls; $call++) {
    $executed = false;
    $locked = false;
    TurnExecutionHelper::executeAllCommand($executed, $locked);
    if ($locked) {
        throw new RuntimeException('엔진이 GAME lock 또는 동결 상태로 진행하지 못했습니다.');
    }
    if ($executed) {
        $executedCount++;
    }
    $gameStor->resetCache();
    $clock = GameClock::fromStorage($gameStor);
    if ($clock->nowTick() !== $fixedNowTick) {
        throw new RuntimeException('엔진 실행 중 manual clock tick이 실제 시간에 의해 변했습니다.');
    }
}

$after = $gameStor->getValues(['year', 'month', 'turntime']);
if (Util::toInt($after['turntime']) > $fixedNowTick) {
    throw new RuntimeException('마지막 실행 tick이 현재 game tick을 넘어갔습니다.');
}
printf(
    "clock_tick=%d clock=%s calls=%d executed_calls=%d before=%d-%02d/%d after=%d-%02d/%d\n",
    $fixedNowTick,
    $clock->formatTick($fixedNowTick, true),
    $engineCalls,
    $executedCount,
    Util::toInt($before['year']),
    Util::toInt($before['month']),
    Util::toInt($before['turntime']),
    Util::toInt($after['year']),
    Util::toInt($after['month']),
    Util::toInt($after['turntime']),
);
