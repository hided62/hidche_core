<?php

namespace sammo;

/**
 * Wall-clock inactivity anchor used only by the reserved reset watchdog.
 *
 * GameClock ticks remain authoritative for gameplay. This timestamp records
 * when a united or invader game last changed phase or actually advanced, so
 * j_autoreset can distinguish a healthy running event from a stalled server.
 */
final class AutoResetProgress
{
    public const STORAGE_KEY = 'autoreset_united_wall_anchor';

    private function __construct()
    {
    }

    public static function record(
        KVStorage $gameStorage,
        ?\DateTimeInterface $wallTime = null,
    ): void {
        $wallTime ??= GameClock::readWallTime();
        $gameStorage->setValue(self::STORAGE_KEY, TimeUtil::format($wallTime, true));
    }

    public static function recordIfAdvanced(
        KVStorage $gameStorage,
        int $beforeTick,
        int $afterTick,
        ?\DateTimeInterface $wallTime = null,
    ): bool {
        if ($afterTick <= $beforeTick) {
            return false;
        }
        self::record($gameStorage, $wallTime);
        return true;
    }
}
