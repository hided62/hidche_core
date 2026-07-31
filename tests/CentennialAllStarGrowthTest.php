<?php

use PHPUnit\Framework\TestCase;
use sammo\CentennialAllStarGrowth;
use sammo\CentennialAllStarGrowthService;
use sammo\General;
use sammo\GameConst;

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('sammo\\', __DIR__ . '/../hwe/sammo', true);

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

    public function testStatResetIsDisabledOnlyForCentennialAllStarPool(): void
    {
        $previousPool = GameConst::$targetGeneralPool;
        try {
            GameConst::$targetGeneralPool = CentennialAllStarGrowthService::POOL_CLASS;
            self::assertFalse(CentennialAllStarGrowthService::isStatResetAllowed());

            GameConst::$targetGeneralPool = 'RandomNameGeneral';
            self::assertTrue(CentennialAllStarGrowthService::isStatResetAllowed());
        } finally {
            GameConst::$targetGeneralPool = $previousPool;
        }
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
        self::assertSame(
            91,
            CentennialAllStarGrowth::statFloor(100, 15, 0.9)
        );
        self::assertSame(
            0.4,
            CentennialAllStarGrowthService::dexTargetRatioForNPCType(3)
        );
        self::assertSame(
            0.4,
            CentennialAllStarGrowthService::dexTargetRatioForNPCType(4)
        );
        self::assertSame(
            1.0,
            CentennialAllStarGrowthService::dexTargetRatioForNPCType(2)
        );
    }

    public function testGeneratedNpcKeepsOrdinaryTotalAndUsesCreationDateTargetFloor(): void
    {
        $vars = [
            'leadership' => 72,
            'strength' => 66,
            'intel' => 12,
            'dex1' => 120000,
            'dex2' => 240000,
            'dex3' => 360000,
            'dex4' => 480000,
            'dex5' => 600000,
            'special' => 'None',
        ];
        $target = [
            'uniqueName' => 'A1000001',
            'leadership' => 100,
            'strength' => 80,
            'intel' => 10,
            'dex' => [900000, 800000, 700000, 600000, 500000],
        ];
        $aux = CentennialAllStarGrowthService::initialAux($target);
        $general = $this->createStateGeneralMock($vars, $aux);

        CentennialAllStarGrowthService::initializeGeneratedNPC($general, $target);
        CentennialAllStarGrowthService::applyTarget(
            $general,
            $target,
            ['startyear' => 180, 'year' => 195, 'month' => 1],
            CentennialAllStarGrowthService::NPC_PROGRESS_MULTIPLIER,
            GameConst::$centennialNpcDexTargetRatio
        );

        self::assertSame(91, $vars['leadership']);
        self::assertSame(73, $vars['strength']);
        self::assertSame(12, $vars['intel']);
        self::assertSame(176, $vars['leadership'] + $vars['strength'] + $vars['intel']);
        self::assertSame(
            [360000, 320000, 280000, 240000, 200000],
            $this->dexValues($vars)
        );
        self::assertSame(19, $aux['granted']['leadership']);
        self::assertSame(7, $aux['granted']['strength']);
        self::assertSame(0, $aux['granted']['intel']);
        self::assertSame(360000, $aux['granted']['dex1']);
    }

    public function testFernandoAndUmaMusumeStartAtOrdinaryNpcTotal(): void
    {
        $pool = json_decode(
            file_get_contents(__DIR__ . '/../hwe/sammo/GeneralPool/Pool/UnderS100.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $columns = array_flip($pool['columns']);
        $targets = [];
        foreach ($pool['data'] as $row) {
            $name = $row[$columns['generalName']];
            if (!in_array($name, ['43·페르난도', '47·우마무스메'], true)) {
                continue;
            }
            $targets[$name] = [
                'leadership' => $row[$columns['leadership']],
                'strength' => $row[$columns['strength']],
                'intel' => $row[$columns['intel']],
            ];
        }
        self::assertCount(2, $targets);

        $generated = [
            'leadership' => 73,
            'strength' => 10,
            'intel' => 67,
        ];
        $fernando = CentennialAllStarGrowthService::calculateGeneratedNPCInitialStats(
            $targets['43·페르난도'],
            $generated
        );
        $umaMusume = CentennialAllStarGrowthService::calculateGeneratedNPCInitialStats(
            $targets['47·우마무스메'],
            $generated
        );

        self::assertSame([
            'leadership' => 67,
            'strength' => 73,
            'intel' => 10,
        ], $fernando);
        self::assertSame([
            'leadership' => 10,
            'strength' => 67,
            'intel' => 73,
        ], $umaMusume);
        self::assertSame(GameConst::$defaultStatNPCTotal, array_sum($fernando));
        self::assertSame(GameConst::$defaultStatNPCTotal, array_sum($umaMusume));
    }

    public function testEveryCandidateKeepsTheGeneratedNpcStatTotal(): void
    {
        $pool = json_decode(
            file_get_contents(__DIR__ . '/../hwe/sammo/GeneralPool/Pool/UnderS100.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $columns = array_flip($pool['columns']);
        $generated = [
            'leadership' => 73,
            'strength' => 67,
            'intel' => 10,
        ];

        foreach ($pool['data'] as $row) {
            $target = [
                'leadership' => $row[$columns['leadership']],
                'strength' => $row[$columns['strength']],
                'intel' => $row[$columns['intel']],
            ];
            $initial = CentennialAllStarGrowthService::calculateGeneratedNPCInitialStats(
                $target,
                $generated
            );
            self::assertSame(
                GameConst::$defaultStatNPCTotal,
                array_sum($initial),
                $row[$columns['generalName']]
            );
            sort($initial, SORT_NUMERIC);
            self::assertSame([10, 67, 73], array_values($initial));
        }
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
        self::assertSame(1.0, $aux['dexTargetRatio']);
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

    public function testNpcDexStopsAtFortyPercentOfHistoricalTarget(): void
    {
        $target = 900000;
        $ratio = GameConst::$centennialNpcDexTargetRatio;

        self::assertSame(0.4, $ratio);
        self::assertSame(
            57600,
            CentennialAllStarGrowthService::calculateDexTargetFloor(
                $target,
                ['startyear' => 180, 'year' => 186, 'month' => 1],
                $ratio
            )
        );
        self::assertSame(
            360000,
            CentennialAllStarGrowthService::calculateDexTargetFloor(
                $target,
                ['startyear' => 180, 'year' => 195, 'month' => 1],
                $ratio
            )
        );
        self::assertSame(
            360000,
            CentennialAllStarGrowthService::calculateDexTargetFloor(
                $target,
                ['startyear' => 180, 'year' => 210, 'month' => 1],
                $ratio
            )
        );
    }

    public function testScenarioConfigKeepsNpcDexTargetRatioAtFortyPercent(): void
    {
        $scenario = json_decode(
            file_get_contents(__DIR__ . '/../hwe/scenario/scenario_915.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertSame(
            GameConst::$centennialNpcDexTargetRatio,
            $scenario['map']['centennialNpcDexTargetRatio']
        );
    }

    public function testNpcDexRatioChangeRemovesOnlyOldEventGrant(): void
    {
        self::assertSame(
            ['value' => 360000, 'granted' => 360000, 'organic' => 0],
            CentennialAllStarGrowth::replaceTarget(810000, 810000, 360000)
        );
        self::assertSame(
            ['value' => 360000, 'granted' => 310000, 'organic' => 50000],
            CentennialAllStarGrowth::replaceTarget(860000, 810000, 360000)
        );
        self::assertSame(
            ['value' => 400000, 'granted' => 0, 'organic' => 400000],
            CentennialAllStarGrowth::replaceTarget(1210000, 810000, 360000)
        );
    }

    public function testExistingNpcDexGrantIsRebasedWithoutChangingFinalStats(): void
    {
        $vars = [
            'leadership' => 91,
            'strength' => 91,
            'intel' => 91,
            'dex1' => 810000,
            'dex2' => 810000,
            'dex3' => 810000,
            'dex4' => 810000,
            'dex5' => 810000,
            'special' => 'None',
        ];
        $aux = [
            'targetId' => 'A1000001',
            'granted' => [
                'leadership' => 76,
                'strength' => 76,
                'intel' => 76,
                'dex1' => 810000,
                'dex2' => 810000,
                'dex3' => 810000,
                'dex4' => 810000,
                'dex5' => 810000,
            ],
            'progressMonth' => 162,
            'milestone' => 4,
            'naturalSpecialDomestic' => null,
            'eventSpecialDomestic' => null,
            'userInitialStats' => null,
        ];
        $general = $this->getMockBuilder(General::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAuxVar', 'setAuxVar', 'getVar', 'updateVar'])
            ->getMock();
        $general->method('getAuxVar')->willReturnCallback(
            static fn(string $key) => $key === CentennialAllStarGrowthService::AUX_KEY
                ? $aux
                : null
        );
        $general->method('getVar')->willReturnCallback(
            static fn(string $key) => $vars[$key] ?? null
        );
        $general->method('updateVar')->willReturnCallback(
            static function (string $key, $value) use (&$vars): void {
                $vars[$key] = $value;
            }
        );
        $general->method('setAuxVar')->willReturnCallback(
            static function (string $key, $value) use (&$aux): void {
                if ($key === CentennialAllStarGrowthService::AUX_KEY) {
                    $aux = $value;
                }
            }
        );

        $result = CentennialAllStarGrowthService::applyTarget(
            $general,
            [
                'uniqueName' => 'A1000001',
                'leadership' => 100,
                'strength' => 100,
                'intel' => 100,
                'dex' => [900000, 900000, 900000, 900000, 900000],
            ],
            ['startyear' => 180, 'year' => 195, 'month' => 1],
            CentennialAllStarGrowthService::NPC_PROGRESS_MULTIPLIER,
            GameConst::$centennialNpcDexTargetRatio
        );

        self::assertTrue($result['changed']);
        self::assertSame(91, $vars['leadership']);
        self::assertSame(91, $vars['strength']);
        self::assertSame(91, $vars['intel']);
        foreach (['dex1', 'dex2', 'dex3', 'dex4', 'dex5'] as $key) {
            self::assertSame(360000, $vars[$key]);
            self::assertSame(360000, $aux['granted'][$key]);
        }
        self::assertSame(0.4, $aux['dexTargetRatio']);
    }

    public function testDexConversionConsumesEventFloorWithoutMonthlyRefill(): void
    {
        $vars = [
            'leadership' => 100,
            'strength' => 100,
            'intel' => 100,
            'dex1' => 216000,
            'dex2' => 489600,
            'dex3' => 360000,
            'dex4' => 360000,
            'dex5' => 360000,
            'special' => 'None',
        ];
        $aux = [
            'targetId' => 'A1000001',
            'granted' => [
                'leadership' => 85,
                'strength' => 85,
                'intel' => 85,
                'dex1' => 360000,
                'dex2' => 360000,
                'dex3' => 360000,
                'dex4' => 360000,
                'dex5' => 360000,
            ],
            'dexConsumed' => [
                'dex1' => 0,
                'dex2' => 0,
                'dex3' => 0,
                'dex4' => 0,
                'dex5' => 0,
            ],
            'progressMonth' => 180,
            'milestone' => 5,
            'naturalSpecialDomestic' => null,
            'eventSpecialDomestic' => null,
            'userInitialStats' => [],
            'dexTargetRatio' => 1.0,
        ];
        $general = $this->createStateGeneralMock($vars, $aux);

        CentennialAllStarGrowthService::reconcileDexConversion(
            $general,
            'dex1',
            'dex2',
            360000,
            216000,
            360000,
            489600,
            0.9
        );

        self::assertSame(216000, $aux['granted']['dex1']);
        self::assertSame(489600, $aux['granted']['dex2']);
        self::assertSame(144000, $aux['dexConsumed']['dex1']);

        CentennialAllStarGrowthService::applyTarget(
            $general,
            [
                'uniqueName' => 'A1000001',
                'leadership' => 100,
                'strength' => 100,
                'intel' => 100,
                'dex' => [360000, 360000, 360000, 360000, 360000],
            ],
            ['startyear' => 180, 'year' => 195, 'month' => 1]
        );

        self::assertSame(216000, $vars['dex1']);
        self::assertSame(489600, $vars['dex2']);
        self::assertSame(216000, $aux['dexFloor']['dex1']);
        self::assertSame(0, CentennialAllStarGrowthService::recordableValue($general, 'dex1'));
        self::assertSame(0, CentennialAllStarGrowthService::recordableValue($general, 'dex2'));

        $vars['dex1'] = 129600;
        $vars['dex2'] = 567360;
        CentennialAllStarGrowthService::reconcileDexConversion(
            $general,
            'dex1',
            'dex2',
            216000,
            129600,
            489600,
            567360,
            0.9
        );
        CentennialAllStarGrowthService::applyTarget(
            $general,
            [
                'uniqueName' => 'A1000001',
                'leadership' => 100,
                'strength' => 100,
                'intel' => 100,
                'dex' => [360000, 360000, 360000, 360000, 360000],
            ],
            ['startyear' => 180, 'year' => 195, 'month' => 1]
        );

        self::assertSame(129600, $vars['dex1']);
        self::assertSame(567360, $vars['dex2']);
        self::assertSame(230400, $aux['dexConsumed']['dex1']);
        self::assertSame(129600, $aux['dexFloor']['dex1']);
        self::assertSame(0, CentennialAllStarGrowthService::recordableValue($general, 'dex1'));
        self::assertSame(0, CentennialAllStarGrowthService::recordableValue($general, 'dex2'));
    }

    public function testNaturalDexConversionDoesNotBecomeEventGrant(): void
    {
        $vars = [
            'dex1' => 216000,
            'dex2' => 129600,
        ];
        $aux = [
            'targetId' => 'A1000001',
            'granted' => [
                'dex1' => 0,
                'dex2' => 0,
            ],
            'dexConsumed' => [
                'dex1' => 0,
                'dex2' => 0,
            ],
            'dexFloor' => [
                'dex1' => 360000,
                'dex2' => 0,
            ],
        ];
        $general = $this->createStateGeneralMock($vars, $aux);

        CentennialAllStarGrowthService::reconcileDexConversion(
            $general,
            'dex1',
            'dex2',
            360000,
            216000,
            0,
            129600,
            0.9
        );

        self::assertSame(0, $aux['granted']['dex1']);
        self::assertSame(0, $aux['granted']['dex2']);
        self::assertSame(144000, $aux['dexConsumed']['dex1']);
        self::assertSame(216000, CentennialAllStarGrowthService::recordableValue($general, 'dex1'));
        self::assertSame(129600, CentennialAllStarGrowthService::recordableValue($general, 'dex2'));
    }

    public function testMixedDexConversionSplitsEventAndNaturalPointsByTotalShare(): void
    {
        $vars = [
            'dex1' => 360000,
            'dex2' => 600000,
        ];
        $aux = [
            'targetId' => 'archer',
            'granted' => [
                'dex1' => 0,
                'dex2' => 900000,
            ],
            'dexConsumed' => [
                'dex1' => 0,
                'dex2' => 0,
            ],
            'dexFloor' => [
                'dex1' => 0,
                'dex2' => 900000,
            ],
        ];
        $general = $this->createStateGeneralMock($vars, $aux);

        CentennialAllStarGrowthService::reconcileDexConversion(
            $general,
            'dex2',
            'dex1',
            1000000,
            600000,
            0,
            360000,
            0.9
        );

        self::assertSame(540000, $aux['granted']['dex2']);
        self::assertSame(324000, $aux['granted']['dex1']);
        self::assertSame(60_000, CentennialAllStarGrowthService::recordableValue($general, 'dex2'));
        self::assertSame(36_000, CentennialAllStarGrowthService::recordableValue($general, 'dex1'));
        self::assertSame(864000, $aux['granted']['dex1'] + $aux['granted']['dex2']);
        self::assertSame(
            96000,
            CentennialAllStarGrowthService::recordableValue($general, 'dex1')
                + CentennialAllStarGrowthService::recordableValue($general, 'dex2')
        );
    }

    public function testArcherCavalryGhostConversionChainKeepsOnlyCurrentTargetBudget(): void
    {
        $vars = $this->emptyGeneralVars();
        $aux = CentennialAllStarGrowthService::initialAux(
            $this->singleDexTarget('archer', 1),
            []
        );
        $general = $this->createStateGeneralMock($vars, $aux);
        $env = ['startyear' => 180, 'year' => 195, 'month' => 1];

        CentennialAllStarGrowthService::applyTarget(
            $general,
            $this->singleDexTarget('archer', 1),
            $env
        );
        $this->convertDex($general, $vars, 'dex2', 'dex1');
        self::assertSame([324000, 540000, 0, 0, 0], $this->dexValues($vars));

        CentennialAllStarGrowthService::applyTarget(
            $general,
            $this->singleDexTarget('cavalry', 2),
            $env
        );
        self::assertSame([0, 0, 900000, 0, 0], $this->dexValues($vars));
        $this->convertDex($general, $vars, 'dex3', 'dex1');
        self::assertSame([324000, 0, 540000, 0, 0], $this->dexValues($vars));

        CentennialAllStarGrowthService::applyTarget(
            $general,
            $this->singleDexTarget('ghost', 3),
            $env
        );
        self::assertSame([0, 0, 0, 900000, 0], $this->dexValues($vars));
        $this->convertDex($general, $vars, 'dex4', 'dex1');

        self::assertSame([324000, 0, 0, 540000, 0], $this->dexValues($vars));
        self::assertSame(864000, array_sum($this->dexValues($vars)));
        foreach (['dex1', 'dex2', 'dex3', 'dex4', 'dex5'] as $key) {
            self::assertSame(0, CentennialAllStarGrowthService::recordableValue($general, $key));
        }
    }

    public function testSelectingInfantryAfterConversionReplacesOldEventBudget(): void
    {
        $vars = $this->emptyGeneralVars();
        $aux = CentennialAllStarGrowthService::initialAux(
            $this->singleDexTarget('archer', 1),
            []
        );
        $general = $this->createStateGeneralMock($vars, $aux);
        $env = ['startyear' => 180, 'year' => 195, 'month' => 1];

        CentennialAllStarGrowthService::applyTarget(
            $general,
            $this->singleDexTarget('archer', 1),
            $env
        );
        $this->convertDex($general, $vars, 'dex2', 'dex1');
        CentennialAllStarGrowthService::applyTarget(
            $general,
            $this->singleDexTarget('infantry', 0),
            $env
        );

        self::assertSame([900000, 0, 0, 0, 0], $this->dexValues($vars));
        self::assertSame(900000, array_sum($aux['granted']));
        self::assertSame(0, CentennialAllStarGrowthService::recordableValue($general, 'dex1'));
    }

    public function testMixedConversionThenReselectionPreservesOnlyPostLossNaturalTotal(): void
    {
        $vars = $this->emptyGeneralVars();
        $aux = CentennialAllStarGrowthService::initialAux(
            $this->singleDexTarget('archer', 1),
            []
        );
        $general = $this->createStateGeneralMock($vars, $aux);
        $env = ['startyear' => 180, 'year' => 195, 'month' => 1];

        CentennialAllStarGrowthService::applyTarget(
            $general,
            $this->singleDexTarget('archer', 1),
            $env
        );
        $vars['dex2'] += 100000;
        $this->convertDex($general, $vars, 'dex2', 'dex1');
        CentennialAllStarGrowthService::applyTarget(
            $general,
            $this->singleDexTarget('infantry', 0),
            $env
        );

        self::assertSame([900000, 60000, 0, 0, 0], $this->dexValues($vars));
        self::assertSame(864000, $aux['granted']['dex1']);
        self::assertSame(0, $aux['granted']['dex2']);
        self::assertSame(96000, array_sum($this->recordableDexValues($general)));
        self::assertSame(960000, array_sum($this->dexValues($vars)));
    }

    public function testReselectionResetsConsumedDexFloorAndTransferredGrant(): void
    {
        $vars = [
            'leadership' => 100,
            'strength' => 100,
            'intel' => 100,
            'dex1' => 216000,
            'dex2' => 489600,
            'dex3' => 360000,
            'dex4' => 360000,
            'dex5' => 360000,
            'special' => 'None',
        ];
        $aux = [
            'targetId' => 'A1000001',
            'granted' => [
                'leadership' => 85,
                'strength' => 85,
                'intel' => 85,
                'dex1' => 216000,
                'dex2' => 489600,
                'dex3' => 360000,
                'dex4' => 360000,
                'dex5' => 360000,
            ],
            'dexConsumed' => [
                'dex1' => 144000,
                'dex2' => 0,
                'dex3' => 0,
                'dex4' => 0,
                'dex5' => 0,
            ],
            'dexFloor' => [
                'dex1' => 216000,
                'dex2' => 360000,
                'dex3' => 360000,
                'dex4' => 360000,
                'dex5' => 360000,
            ],
            'progressMonth' => 180,
            'milestone' => 5,
            'naturalSpecialDomestic' => null,
            'eventSpecialDomestic' => null,
            'userInitialStats' => [],
            'dexTargetRatio' => 1.0,
        ];
        $general = $this->createStateGeneralMock($vars, $aux);

        CentennialAllStarGrowthService::applyTarget(
            $general,
            [
                'uniqueName' => 'A1000002',
                'leadership' => 100,
                'strength' => 100,
                'intel' => 100,
                'dex' => [400000, 400000, 400000, 400000, 400000],
            ],
            ['startyear' => 180, 'year' => 195, 'month' => 1]
        );

        self::assertSame(400000, $vars['dex1']);
        self::assertSame(400000, $vars['dex2']);
        self::assertSame(0, $aux['dexConsumed']['dex1']);
        self::assertSame(400000, $aux['dexFloor']['dex1']);
        self::assertSame(400000, $aux['granted']['dex1']);
        self::assertSame(400000, $aux['granted']['dex2']);
    }

    public function testUserDexStillReachesFullHistoricalTarget(): void
    {
        self::assertSame(
            900000,
            CentennialAllStarGrowthService::calculateDexTargetFloor(
                900000,
                ['startyear' => 180, 'year' => 195, 'month' => 1]
            )
        );
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

    private function createStateGeneralMock(array &$vars, array &$aux): General
    {
        $general = $this->getMockBuilder(General::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAuxVar', 'setAuxVar', 'getVar', 'updateVar'])
            ->getMock();
        $general->method('getAuxVar')->willReturnCallback(
            static function (string $key) use (&$aux) {
                return $key === CentennialAllStarGrowthService::AUX_KEY
                    ? $aux
                    : null;
            }
        );
        $general->method('getVar')->willReturnCallback(
            static function (string $key) use (&$vars) {
                return $vars[$key] ?? null;
            }
        );
        $general->method('updateVar')->willReturnCallback(
            static function (string $key, $value) use (&$vars): void {
                $vars[$key] = $value;
            }
        );
        $general->method('setAuxVar')->willReturnCallback(
            static function (string $key, $value) use (&$aux): void {
                if ($key === CentennialAllStarGrowthService::AUX_KEY) {
                    $aux = $value;
                }
            }
        );
        return $general;
    }

    private function emptyGeneralVars(): array
    {
        return [
            'leadership' => 100,
            'strength' => 100,
            'intel' => 100,
            'dex1' => 0,
            'dex2' => 0,
            'dex3' => 0,
            'dex4' => 0,
            'dex5' => 0,
            'special' => 'None',
        ];
    }

    private function singleDexTarget(string $id, int $dexIndex): array
    {
        $dex = [0, 0, 0, 0, 0];
        $dex[$dexIndex] = 900000;
        return [
            'uniqueName' => $id,
            'leadership' => 100,
            'strength' => 100,
            'intel' => 100,
            'dex' => $dex,
        ];
    }

    private function convertDex(
        General $general,
        array &$vars,
        string $sourceKey,
        string $destinationKey
    ): void {
        $sourceBefore = $vars[$sourceKey];
        $destinationBefore = $vars[$destinationKey];
        $cut = (int) ($sourceBefore * 0.4);
        $add = (int) ($cut * 0.9);
        $vars[$sourceKey] -= $cut;
        $vars[$destinationKey] += $add;
        CentennialAllStarGrowthService::reconcileDexConversion(
            $general,
            $sourceKey,
            $destinationKey,
            $sourceBefore,
            $vars[$sourceKey],
            $destinationBefore,
            $vars[$destinationKey],
            0.9
        );
    }

    private function dexValues(array $vars): array
    {
        return [
            $vars['dex1'],
            $vars['dex2'],
            $vars['dex3'],
            $vars['dex4'],
            $vars['dex5'],
        ];
    }

    private function recordableDexValues(General $general): array
    {
        return array_map(
            static fn(string $key): int => CentennialAllStarGrowthService::recordableValue(
                $general,
                $key
            ),
            ['dex1', 'dex2', 'dex3', 'dex4', 'dex5']
        );
    }
}
