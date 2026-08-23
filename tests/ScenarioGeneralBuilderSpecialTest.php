<?php

use PHPUnit\Framework\TestCase;
use sammo\GameConst;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\Scenario\GeneralBuilder;
use sammo\Util;

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('sammo\\', __DIR__ . '/../hwe/sammo', true);

require_once __DIR__ . '/../hwe/func_converter.php';
require_once __DIR__ . '/../hwe/sammo/ActionLogger.php';
require_once __DIR__ . '/../hwe/sammo/GameConstBase.php';
require_once __DIR__ . '/../hwe/d_setting/GameConst.php';
require_once __DIR__ . '/../hwe/sammo/Scenario/GeneralBuilder.php';

final class ScenarioGeneralBuilderSpecialTest extends TestCase
{
    public function testPreparedStoredIconsAreReusedWithoutChangingTheEnvironment(): void
    {
        $environment = [
            'stored_icons' => ['.' => ['default.jpg']],
            'icon_path' => '.',
        ];

        self::assertSame($environment, GeneralBuilder::prepareEnvironment($environment));
    }

    public function testAsiaPossessionScenarioWarSpecialsUseWarSlot(): void
    {
        $scenario = json_decode(
            file_get_contents(__DIR__ . '/../hwe/scenario/scenario_2702.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $specials = array_values(array_unique(array_filter(array_column($scenario['general'], 12))));

        self::assertNotEmpty($specials);
        foreach ($specials as $special) {
            $builder = $this->newBuilder()->setSpecialSingle($special);

            self::assertSame(
                GameConst::$defaultSpecialDomestic,
                $this->readProperty($builder, 'specialDomestic'),
                "{$special} must not occupy the domestic-special slot"
            );
            self::assertSame(
                "che_{$special}",
                $this->readProperty($builder, 'specialWar'),
                "{$special} must occupy the war-special slot"
            );
        }
    }

    public function testScenarioDomesticSpecialStillUsesDomesticSlot(): void
    {
        $builder = $this->newBuilder()->setSpecialSingle('경작');

        self::assertSame('che_경작', $this->readProperty($builder, 'specialDomestic'));
        self::assertSame(GameConst::$defaultSpecialWar, $this->readProperty($builder, 'specialWar'));
    }

    public function testCentennialEventWarSpecialCanStillBeAssignedToDomesticSlotExplicitly(): void
    {
        $builder = $this->newBuilder()->setSpecial('che_event_위압', GameConst::$defaultSpecialWar);

        self::assertSame('che_event_위압', $this->readProperty($builder, 'specialDomestic'));
        self::assertSame(GameConst::$defaultSpecialWar, $this->readProperty($builder, 'specialWar'));
    }

    private function newBuilder(): GeneralBuilder
    {
        return new GeneralBuilder(
            new RandUtil(new LiteHashDRBG(Util::simpleSerialize(self::class))),
            '특기 슬롯 검사',
            false,
            null,
            0
        );
    }

    private function readProperty(GeneralBuilder $builder, string $property): mixed
    {
        $reflection = new ReflectionProperty($builder, $property);
        return $reflection->getValue($builder);
    }
}
