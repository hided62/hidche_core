<?php

namespace sammo;

use PHPUnit\Framework\TestCase;
use sammo\DTO\VoteInfo;

require_once __DIR__ . '/../src/sammo/GameClock.php';
require_once __DIR__ . '/../hwe/sammo/DTO/VoteInfo.php';

final class VoteGameClockTest extends TestCase
{
    public function testLegacyDatesAreConvertedToStableTicksAndProjectedAgain(): void
    {
        $base = new \DateTimeImmutable('2035-01-01 00:00:00.000000');
        $clock = new GameClock($base, 60, 0, GameClock::MODE_MANUAL, $base);
        $raw = [
            'id' => 7,
            'title' => '논리 시계 투표',
            'multipleOptions' => 1,
            'opener' => 'SYSTEM',
            'startDate' => '2035-01-01 01:00:00',
            'endDate' => '2035-01-01 03:00:00',
            'options' => ['찬성', '반대'],
        ];

        $stored = VoteInfo::normalizeGameStorage($raw, $clock);

        self::assertSame(GameClock::TICKS_PER_TURN, $stored['startTick']);
        self::assertSame(GameClock::TICKS_PER_TURN * 3, $stored['endTick']);
        self::assertSame('2035-01-01 01:00:00', $stored['startDate']);
        self::assertSame('2035-01-01 03:00:00', $stored['endDate']);
    }

    public function testStoredTicksRemainAuthoritativeWhenProjectionBaseChanges(): void
    {
        $oldBase = new \DateTimeImmutable('2035-01-01 00:00:00');
        $newBase = new \DateTimeImmutable('2040-05-01 12:00:00');
        $clock = new GameClock($newBase, 60, 0, GameClock::MODE_MANUAL, $newBase);
        $stored = [
            'id' => 8,
            'title' => 'tick 우선',
            'multipleOptions' => 1,
            'opener' => null,
            'startDate' => $oldBase->format('Y-m-d H:i:s'),
            'endDate' => $oldBase->modify('+1 hour')->format('Y-m-d H:i:s'),
            'startTick' => GameClock::TICKS_PER_TURN * 2,
            'endTick' => GameClock::TICKS_PER_TURN * 4,
            'options' => ['A'],
        ];

        $normalized = VoteInfo::normalizeGameStorage($stored, $clock);
        self::assertSame('2040-05-01 14:00:00', $normalized['startDate']);
        self::assertSame('2040-05-01 16:00:00', $normalized['endDate']);
    }
}
