#!/usr/bin/env php
<?php

declare(strict_types=1);

use sammo\DB;
use sammo\GameClock;
use sammo\KVStorage;
use sammo\Util;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$_SERVER['REMOTE_ADDR'] ??= '127.0.0.1';
$_SERVER['REQUEST_URI'] ??= '/cli/game-clock';

require dirname(__DIR__) . '/hwe/lib.php';
require dirname(__DIR__) . '/hwe/func.php';

/** @return never */
function gameClockUsage(int $exitCode = 0): void
{
    $stream = $exitCode === 0 ? STDOUT : STDERR;
    fwrite($stream, <<<'TEXT'
Usage:
  php scripts/game-clock.php --status
  php scripts/game-clock.php --mode=manual|realtime --apply
  php scripts/game-clock.php --advance-turns=N --apply
  php scripts/game-clock.php --advance-ticks=N --apply

Mutation commands require --apply and the GAME lock. Explicit advancement
switches the clock to manual mode, so subsequent engine runs never consult the
wall clock. Use --mode=realtime to resume wall-clock-paced progression.
TEXT);
    exit($exitCode);
}

/** @return int */
function signedIntegerOption(array $options, string $name): int
{
    $value = $options[$name] ?? null;
    if (!is_string($value) || preg_match('/^-?[0-9]+$/', $value) !== 1) {
        throw new InvalidArgumentException("--{$name}에는 정수를 지정해야 합니다.");
    }
    $result = filter_var($value, FILTER_VALIDATE_INT);
    if ($result === false) {
        throw new InvalidArgumentException("--{$name} 값이 INT64 범위를 벗어났습니다.");
    }
    return $result;
}

function printGameClockState(KVStorage $gameStor): void
{
    $gameStor->resetCache();
    $clock = GameClock::fromStorage($gameStor);
    $nowTick = $clock->nowTick();
    $state = $gameStor->getValues(['year', 'month', 'turntime', 'turnterm']);
    printf(
        "mode=%s game=%d-%02d now_tick=%d now=%s last_tick=%d last=%s turnterm=%dm ticks_per_turn=%d ticks_per_second=%d\n",
        $clock->getMode(),
        Util::toInt($state['year']),
        Util::toInt($state['month']),
        $nowTick,
        $clock->formatTick($nowTick, true),
        Util::toInt($state['turntime']),
        $clock->formatTick(Util::toInt($state['turntime']), true),
        Util::toInt($state['turnterm']),
        GameClock::TICKS_PER_TURN,
        $clock->ticksPerSecond(),
    );
}

$options = getopt('', ['help', 'status', 'mode:', 'advance-turns:', 'advance-ticks:', 'apply']);
if (isset($options['help'])) {
    gameClockUsage();
}

$commands = array_filter([
    'status' => isset($options['status']),
    'mode' => array_key_exists('mode', $options),
    'advance-turns' => array_key_exists('advance-turns', $options),
    'advance-ticks' => array_key_exists('advance-ticks', $options),
]);
if (count($commands) !== 1) {
    gameClockUsage(2);
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

if (isset($commands['status'])) {
    printGameClockState($gameStor);
    exit(0);
}
if (!isset($options['apply'])) {
    fwrite(STDERR, "변경 명령에는 --apply가 필요합니다.\n");
    exit(2);
}
if (!\sammo\tryLock()) {
    fwrite(STDERR, "GAME lock을 획득하지 못했습니다.\n");
    exit(3);
}

try {
    $clock = GameClock::fromStorage($gameStor);
    $currentTick = $clock->nowTick();

    if (isset($commands['mode'])) {
        $mode = (string)$options['mode'];
        if (!in_array($mode, [GameClock::MODE_MANUAL, GameClock::MODE_REALTIME], true)) {
            throw new InvalidArgumentException('--mode는 manual 또는 realtime이어야 합니다.');
        }
        $clock->persistTick($gameStor, $currentTick, $mode);
    } else {
        if (isset($commands['advance-turns'])) {
            $nextTick = $clock->addTurns($currentTick, signedIntegerOption($options, 'advance-turns'));
        } else {
            $nextTick = GameClock::addTicks($currentTick, signedIntegerOption($options, 'advance-ticks'));
        }
        $clock->persistTick($gameStor, $nextTick, GameClock::MODE_MANUAL);
    }
} finally {
    $gameStor->resetCache();
    \sammo\unlock();
}

printGameClockState($gameStor);
