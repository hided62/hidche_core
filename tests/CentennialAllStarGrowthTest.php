<?php

use PHPUnit\Framework\TestCase;
use sammo\CentennialAllStarGrowth;
use sammo\CentennialAllStarGrowthService;

require_once __DIR__ . '/../hwe/sammo/CentennialAllStarGrowthService.php';

final class CentennialAllStarGrowthTest extends TestCase
{
    public function testProgressMilestones(): void
    {
        self::assertSame(0.0, CentennialAllStarGrowth::progress(180, 180, 1));
        self::assertEqualsWithDelta(0.2, CentennialAllStarGrowth::progress(180, 183, 1), 0.000001);
        self::assertEqualsWithDelta(0.4, CentennialAllStarGrowth::progress(180, 186, 1), 0.000001);
        self::assertSame(1.0, CentennialAllStarGrowth::progress(180, 195, 1));
        self::assertSame(1.0, CentennialAllStarGrowth::progress(180, 210, 1));
    }

    public function testLowStatGrowsWhileHigherStatStays(): void
    {
        $floor = CentennialAllStarGrowth::statFloor(90, 15, 0.6);
        self::assertSame(60, $floor);
        self::assertSame(
            ['value' => 70, 'granted' => 0, 'delta' => 0],
            CentennialAllStarGrowth::advance(70, 0, $floor)
        );
        self::assertSame(
            ['value' => 60, 'granted' => 10, 'delta' => 10],
            CentennialAllStarGrowth::advance(50, 0, $floor)
        );
    }

    public function testReselectDropsOldGrantAndKeepsOrganicGrowth(): void
    {
        self::assertSame(
            ['value' => 82, 'granted' => 7, 'organic' => 75],
            CentennialAllStarGrowth::replaceTarget(86, 11, 82)
        );
        self::assertSame(
            ['value' => 90, 'granted' => 0, 'organic' => 90],
            CentennialAllStarGrowth::replaceTarget(101, 11, 82)
        );
    }

    public function testRepeatedAdvanceIsIdempotent(): void
    {
        $first = CentennialAllStarGrowth::advance(50, 0, 60);
        $second = CentennialAllStarGrowth::advance($first['value'], $first['granted'], 60);
        self::assertSame(60, $second['value']);
        self::assertSame(10, $second['granted']);
        self::assertSame(0, $second['delta']);
        self::assertSame(50, CentennialAllStarGrowth::recordableValue(60, 10));
    }

    public function testDexUsesSlowEarlyCurve(): void
    {
        self::assertSame(0, CentennialAllStarGrowth::dexFloor(900000, 0));
        self::assertSame(36000, CentennialAllStarGrowth::dexFloor(900000, 0.2));
        self::assertSame(144000, CentennialAllStarGrowth::dexFloor(900000, 0.4));
        self::assertSame(900000, CentennialAllStarGrowth::dexFloor(900000, 1));
    }

    public function testUntrackedGeneralKeepsFullHallValue(): void
    {
        self::assertSame(
            123456,
            CentennialAllStarGrowthService::recordableRawValue(
                ['dex1' => 123456, 'aux' => '{}'],
                'dex1'
            )
        );
    }
}
