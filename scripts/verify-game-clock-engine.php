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
while (ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(true);

$options = getopt('', ['apply', 'engine-calls:', 'until-unification', 'max-months:']);
if (!isset($options['apply'])) {
    fwrite(STDERR, "Usage: php scripts/verify-game-clock-engine.php --apply [--engine-calls=N] [--until-unification --max-months=N]\n");
    exit(2);
}
$untilUnification = isset($options['until-unification']);
$engineCalls = filter_var($options['engine-calls'] ?? ($untilUnification ? '100' : '1'), FILTER_VALIDATE_INT);
if ($engineCalls === false || $engineCalls < 1 || $engineCalls > 1000) {
    throw new InvalidArgumentException('--engine-calls는 1..1000이어야 합니다.');
}
$maxMonths = filter_var($options['max-months'] ?? '2400', FILTER_VALIDATE_INT);
if ($maxMonths === false || $maxMonths < 1 || $maxMonths > 10000) {
    throw new InvalidArgumentException('--max-months는 1..10000이어야 합니다.');
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$clock = GameClock::fromStorage($gameStor);
if ($clock->getMode() !== GameClock::MODE_MANUAL) {
    throw new RuntimeException('재현 검증은 manual clock에서만 실행할 수 있습니다.');
}

$fixedNowTick = $clock->nowTick();
$before = $gameStor->getValues(['year', 'month', 'turntime']);

if ($untilUnification) {
    $startedAt = GameClock::readWallTime();
    $startProjection = $clock->formatTick($fixedNowTick, true);
    $advancedMonths = 0;
    $totalEngineCalls = 0;
    while ($advancedMonths < $maxMonths) {
        $gameStor->resetCache();
        $state = $gameStor->getValues(['year', 'month', 'turntime', 'turnterm', 'isunited']);
        if (in_array(Util::toInt($state['isunited']), [2, 3], true)) {
            break;
        }

        $beforeYearMonth = Util::joinYearMonth(Util::toInt($state['year']), Util::toInt($state['month']));
        $clock = GameClock::fromStorage($gameStor);
        $nextMonthBoundary = $clock->addTurns(
            \sammo\cutTurn(Util::toInt($state['turntime']), Util::toInt($state['turnterm'])),
            1,
        );
        $nextMonthTick = GameClock::addTicks($nextMonthBoundary, 1);

        if (!\sammo\tryLock()) {
            throw new RuntimeException('manual clock 전진을 위한 GAME lock을 획득하지 못했습니다.');
        }
        try {
            $clock->persistTick($gameStor, $nextMonthTick, GameClock::MODE_MANUAL);
        } finally {
            $gameStor->resetCache();
            \sammo\unlock();
        }

        $monthAdvanced = false;
        for ($call = 0; $call < $engineCalls; $call++) {
            $executed = false;
            $locked = false;
            TurnExecutionHelper::executeAllCommand($executed, $locked);
            $totalEngineCalls++;
            $gameStor->resetCache();
            $clock = GameClock::fromStorage($gameStor);
            if ($clock->getMode() !== GameClock::MODE_MANUAL || $clock->nowTick() !== $nextMonthTick) {
                throw new RuntimeException('실제 턴 엔진 실행 중 manual clock 상태가 벽시계에 의해 바뀌었습니다.');
            }
            $afterCall = $gameStor->getValues(['year', 'month', 'turntime', 'isunited']);
            if (Util::toInt($afterCall['turntime']) > $nextMonthTick) {
                throw new RuntimeException('마지막 실행 tick이 현재 manual clock tick을 넘어갔습니다.');
            }
            if (in_array(Util::toInt($afterCall['isunited']), [2, 3], true)) {
                $monthAdvanced = true;
                break;
            }
            // turntime is the authoritative completed schedule boundary. Some
            // legacy monthly state is cached until the next storage read, so do
            // not spin merely because year/month from that same call is stale.
            if (Util::toInt($afterCall['turntime']) >= $nextMonthBoundary) {
                $monthAdvanced = true;
                break;
            }
            $afterYearMonth = Util::joinYearMonth(Util::toInt($afterCall['year']), Util::toInt($afterCall['month']));
            if ($afterYearMonth !== $beforeYearMonth) {
                $monthAdvanced = true;
                break;
            }
            if ($locked) {
                throw new RuntimeException('통일 전 실제 턴 엔진이 GAME lock 또는 동결 상태에 머물렀습니다.');
            }
        }
        if (!$monthAdvanced) {
            throw new RuntimeException("한 달을 {$engineCalls}회 엔진 호출 안에 완료하지 못했습니다.");
        }

        $advancedMonths++;
        if ($advancedMonths % 12 === 0) {
            $gameStor->resetCache();
            printf(
                "progress months=%d game=%d-%02d clock_tick=%d nations=%d engine_calls=%d\n",
                $advancedMonths,
                Util::toInt($gameStor->year),
                Util::toInt($gameStor->month),
                GameClock::fromStorage($gameStor)->nowTick(),
                Util::toInt($db->queryFirstField('SELECT COUNT(*) FROM nation WHERE level > 0')),
                $totalEngineCalls,
            );
        }
    }

    $gameStor->resetCache();
    $finalState = $gameStor->getValues(['year', 'month', 'turntime', 'isunited']);
    if (!in_array(Util::toInt($finalState['isunited']), [2, 3], true)) {
        throw new RuntimeException("{$maxMonths}개월 안에 천하통일에 도달하지 못했습니다.");
    }
    $finalClock = GameClock::fromStorage($gameStor);
    printf(
        "UNIFIED months=%d engine_calls=%d game=%d-%02d isunited=%d clock_tick=%d projected=%s start_projected=%s wall_elapsed=%.6f\n",
        $advancedMonths,
        $totalEngineCalls,
        Util::toInt($finalState['year']),
        Util::toInt($finalState['month']),
        Util::toInt($finalState['isunited']),
        $finalClock->nowTick(),
        $finalClock->formatNow(true),
        $startProjection,
        (float)GameClock::readWallTime()->format('U.u') - (float)$startedAt->format('U.u'),
    );
    exit(0);
}

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
