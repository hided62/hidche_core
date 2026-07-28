<?php

use PHPUnit\Framework\TestCase;
use sammo\CentennialAllStarGrowth;
use sammo\CentennialAllStarGrowthService;

require_once __DIR__ . '/../hwe/sammo/ActionLogger.php';
require_once __DIR__ . '/../hwe/sammo/GameConstBase.php';
require_once __DIR__ . '/../hwe/d_setting/GameConst.php';
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

    public function testMAndGGeneralsAdvanceAtNinetyPercentProgress(): void
    {
        self::assertSame(
            CentennialAllStarGrowthService::NPC_PROGRESS_MULTIPLIER,
            CentennialAllStarGrowthService::progressMultiplierForNPCType(3)
        );
        self::assertSame(
            CentennialAllStarGrowthService::NPC_PROGRESS_MULTIPLIER,
            CentennialAllStarGrowthService::progressMultiplierForNPCType(4)
        );
        self::assertSame(1.0, CentennialAllStarGrowthService::progressMultiplierForNPCType(2));
        self::assertEqualsWithDelta(
            0.36,
            CentennialAllStarGrowthService::calculateProgress(180, 186, 1, 0.9),
            0.000001
        );
        self::assertEqualsWithDelta(
            0.9,
            CentennialAllStarGrowthService::calculateProgress(180, 195, 1, 0.9),
            0.000001
        );
        self::assertEqualsWithDelta(
            0.9,
            CentennialAllStarGrowthService::calculateProgress(180, 210, 1, 0.9),
            0.000001
        );
    }

    public function testProgressMultiplierMustStayWithinUnitInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CentennialAllStarGrowthService::calculateProgress(180, 195, 1, 1.01);
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

    public function testUserInitialStatsPreserveCandidateShapeAtOrdinaryTotal(): void
    {
        $initial = CentennialAllStarGrowthService::calculateUserInitialStats([
            'leadership' => 80,
            'strength' => 70,
            'intel' => 50,
        ]);

        self::assertSame([
            'leadership' => 65,
            'strength' => 58,
            'intel' => 42,
        ], $initial);
        self::assertSame(165, array_sum($initial));
        self::assertLessThanOrEqual(80, $initial['leadership']);
        self::assertLessThanOrEqual(70, $initial['strength']);
        self::assertLessThanOrEqual(50, $initial['intel']);
    }

    public function testUserInitialStatsDoNotRaiseCandidateBelowOrdinaryTotal(): void
    {
        self::assertSame([
            'leadership' => 60,
            'strength' => 45,
            'intel' => 30,
        ], CentennialAllStarGrowthService::calculateUserInitialStats([
            'leadership' => 60,
            'strength' => 45,
            'intel' => 30,
        ]));
    }

    public function testCurrentReselectionBaselineUsesCurrentYearProgress(): void
    {
        $target = [
            'leadership' => 100,
            'strength' => 15,
            'intel' => 100,
        ];

        self::assertSame([
            'leadership' => 75,
            'strength' => 15,
            'intel' => 75,
        ], CentennialAllStarGrowthService::calculateUserCurrentTargetStats(
            $target,
            ['startyear' => 180, 'year' => 180, 'month' => 1]
        ));
        self::assertSame([
            'leadership' => 83,
            'strength' => 15,
            'intel' => 83,
        ], CentennialAllStarGrowthService::calculateUserCurrentTargetStats(
            $target,
            ['startyear' => 180, 'year' => 192, 'month' => 1]
        ));
        self::assertSame([
            'leadership' => 100,
            'strength' => 15,
            'intel' => 100,
        ], CentennialAllStarGrowthService::calculateUserCurrentTargetStats(
            $target,
            ['startyear' => 180, 'year' => 195, 'month' => 1]
        ));
    }

    public function testInitialUserGrantMakesInitialAllocationReplaceable(): void
    {
        $initial = CentennialAllStarGrowthService::calculateUserInitialStats([
            'uniqueName' => 'A1000001',
            'leadership' => 80,
            'strength' => 70,
            'intel' => 50,
        ]);
        $aux = CentennialAllStarGrowthService::initialAux([
            'uniqueName' => 'A1000001',
        ], $initial);

        self::assertSame('A1000001', $aux['targetId']);
        self::assertSame($initial, $aux['userInitialStats']);
        self::assertSame(50, $aux['granted']['leadership']);
        self::assertSame(43, $aux['granted']['strength']);
        self::assertSame(27, $aux['granted']['intel']);
    }

    public function testLegacyInitialGrantKeepsOnlyGrowthBeyondCreationRange(): void
    {
        self::assertSame(
            35,
            CentennialAllStarGrowthService::calculateLegacyUserGrant(50, 0)
        );
        self::assertSame(
            75,
            CentennialAllStarGrowthService::calculateLegacyUserGrant(90, 40)
        );
        self::assertSame(
            105,
            CentennialAllStarGrowthService::calculateLegacyUserGrant(140, 40)
        );
        self::assertSame(
            35,
            140 - CentennialAllStarGrowthService::calculateLegacyUserGrant(140, 40)
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
