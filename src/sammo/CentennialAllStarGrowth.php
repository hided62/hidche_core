<?php

namespace sammo;

final class CentennialAllStarGrowth
{
    public const DEFAULT_GROWTH_YEARS = 15;

    public static function progress(
        int $startYear,
        int $year,
        int $month,
        int $growthYears = self::DEFAULT_GROWTH_YEARS
    ): float {
        if ($growthYears <= 0) {
            throw new \InvalidArgumentException('growthYears must be positive');
        }
        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException('month must be between 1 and 12');
        }

        $elapsedMonths = max(0, ($year - $startYear) * 12 + $month - 1);
        return min(1.0, $elapsedMonths / ($growthYears * 12));
    }

    public static function statFloor(int $target, int $minimum, float $progress): int
    {
        $progress = self::clampProgress($progress);
        if ($target <= $minimum) {
            return $target;
        }
        return min($target, (int) floor($minimum + ($target - $minimum) * $progress));
    }

    public static function dexFloor(int $target, float $progress): int
    {
        $progress = self::clampProgress($progress);
        return min($target, (int) floor($target * $progress * $progress));
    }

    /**
     * @return array{value:int, granted:int, delta:int}
     */
    public static function advance(int $current, int $granted, int $floor): array
    {
        $delta = max(0, $floor - $current);
        return [
            'value' => $current + $delta,
            'granted' => max(0, $granted) + $delta,
            'delta' => $delta,
        ];
    }

    /**
     * @return array{value:int, granted:int, organic:int}
     */
    public static function replaceTarget(int $current, int $oldGranted, int $newFloor): array
    {
        $organic = max(0, $current - max(0, $oldGranted));
        $value = max($organic, $newFloor);
        return [
            'value' => $value,
            'granted' => $value - $organic,
            'organic' => $organic,
        ];
    }

    public static function recordableValue(int $current, int $granted): int
    {
        return max(0, $current - max(0, $granted));
    }

    private static function clampProgress(float $progress): float
    {
        return max(0.0, min(1.0, $progress));
    }
}
