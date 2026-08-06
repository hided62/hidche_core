<?php

namespace sammo;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/sammo/GameClock.php';
require_once __DIR__ . '/../hwe/sammo/TurnExecutionHelper.php';

final class GameClockTest extends TestCase
{
    /** @dataProvider supportedTurnTerms */
    public function testEachSupportedTurnTermHasIntegerTicksPerSecond(int $turnTerm, int $ticksPerSecond): void
    {
        $base = new \DateTimeImmutable('2026-08-03 00:00:00.000000');
        $clock = new GameClock($base, $turnTerm, 0, GameClock::MODE_MANUAL, $base);

        self::assertSame($ticksPerSecond, $clock->ticksPerSecond());
        self::assertSame(GameClock::TICKS_PER_TURN, $clock->ticksFromMinutes($turnTerm));
    }

    public static function supportedTurnTerms(): array
    {
        return [
            '1 minute' => [1, 600_000],
            '2 minutes' => [2, 300_000],
            '5 minutes' => [5, 120_000],
            '10 minutes' => [10, 60_000],
            '60 minutes' => [60, 10_000],
            '120 minutes' => [120, 5_000],
        ];
    }

    public function testTickFormulaAndDisplayProjectionRoundTrip(): void
    {
        $base = new \DateTimeImmutable('2026-08-03 12:34:56.000000');
        $clock = new GameClock($base, 60, 0, GameClock::MODE_MANUAL, $base);
        $tick = GameClock::TICKS_PER_TURN * 7 + 12_345;

        self::assertSame(['turn' => 7, 'subTick' => 12_345], $clock->splitTick($tick));
        self::assertSame($tick, $clock->dateTimeToTick($clock->tickToDateTime($tick)));
        self::assertSame('2026-08-03 19:34:57.234500', $clock->formatTick($tick, true));
    }

    public function testManualModeDoesNotReadWallClock(): void
    {
        $base = new \DateTimeImmutable('2026-08-03 00:00:00.000000');
        $wallRead = false;
        $clock = new GameClock(
            $base,
            10,
            123_456,
            GameClock::MODE_MANUAL,
            $base,
            function () use (&$wallRead): \DateTimeImmutable {
                $wallRead = true;
                return new \DateTimeImmutable('2099-01-01 00:00:00.000000');
            },
        );

        self::assertSame(123_456, $clock->nowTick());
        self::assertSame($clock->formatTick(123_456), $clock->formatNow());
        self::assertFalse($wallRead);
    }

    public function testNegativeTickProjectionAndBaseRecalculation(): void
    {
        $projected = new \DateTimeImmutable('2026-08-03 12:00:00.123400');
        $tick = -36_001_234;
        $base = GameClock::baseTimeForProjection($projected, $tick, 60);
        $clock = new GameClock($base, 60, $tick, GameClock::MODE_MANUAL, $projected);

        self::assertSame($tick, $clock->dateTimeToTick($clock->tickToDateTime($tick)));
        self::assertSame('2026-08-03 12:00:00.123400', $clock->formatTick($tick, true));
        self::assertSame(['turn' => -2, 'subTick' => 35_998_766], $clock->splitTick($tick));
    }

    public function testRealtimeModeAdvancesFromAnchorWithoutDatabaseNow(): void
    {
        $base = new \DateTimeImmutable('2026-08-03 00:00:00.000000');
        $wallAnchor = new \DateTimeImmutable('2026-08-03 10:00:00.000000');
        $clock = new GameClock(
            $base,
            120,
            100,
            GameClock::MODE_REALTIME,
            $wallAnchor,
            fn (): \DateTimeImmutable => new \DateTimeImmutable('2026-08-03 10:00:01.500000'),
        );

        self::assertSame(7_600, $clock->nowTick());
    }

    public function testStorageInitializationDetectionSupportsMixedLegacyProfiles(): void
    {
        $logicalStorage = $this->createMock(KVStorage::class);
        $logicalStorage->method('getValues')->willReturn([
            'clock_base_time' => '2026-08-03 00:00:00.000000',
            'clock_tick' => 0,
            'clock_mode' => GameClock::MODE_REALTIME,
            'clock_wall_anchor' => '2026-08-03 00:00:00.000000',
            'turnterm' => 60,
        ]);
        self::assertTrue(GameClock::isInitialized($logicalStorage));

        $legacyStorage = $this->createMock(KVStorage::class);
        $legacyStorage->method('getValues')->willReturn([
            'turnterm' => 60,
        ]);
        self::assertFalse(GameClock::isInitialized($legacyStorage));

        $reservedResetStorage = $this->createMock(KVStorage::class);
        $reservedResetStorage->method('getValues')->willReturn([]);
        self::assertFalse(GameClock::isInitialized($reservedResetStorage));

        $partialStorage = $this->createMock(KVStorage::class);
        $partialStorage->method('getValues')->willReturn([
            'clock_tick' => 0,
            'turnterm' => 60,
        ]);
        $this->expectException(\RuntimeException::class);
        GameClock::isInitialized($partialStorage);
    }

    public function testTickArithmeticRejectsValuesThatJavaScriptCannotRepresentExactly(): void
    {
        self::assertSame(GameClock::MAX_SAFE_TICK, GameClock::addTicks(GameClock::MAX_SAFE_TICK - 1, 1));

        $this->expectException(\OverflowException::class);
        GameClock::addTicks(GameClock::MAX_SAFE_TICK, 1);
    }

    public function testGlobalCompletionTickIsMonotonicAcrossSubTickExecution(): void
    {
        self::assertSame(36_000_000, TurnExecutionHelper::monotonicCompletionTick(36_000_000, 35_500_000));
        self::assertSame(36_500_000, TurnExecutionHelper::monotonicCompletionTick(36_000_000, 36_500_000));
    }
}
