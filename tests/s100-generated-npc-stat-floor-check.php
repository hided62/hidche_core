<?php

declare(strict_types=1);

use sammo\CentennialAllStarGrowthService;
use sammo\DB;
use sammo\GameConst;
use sammo\General;
use sammo\GeneralPool\SPoolUnderU100;
use sammo\Json;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\Util;

const APP_ROOT = '/var/www/html';
const TARGET_NAMES = ['43·페르난도', '47·우마무스메'];

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/s100-generated-npc-stat-floor-check';

require APP_ROOT . '/hwe/lib.php';
require APP_ROOT . '/hwe/func.php';

$env = [
    'startyear' => 180,
    'year' => 180,
    'month' => 12,
    'fiction' => [1],
    'show_img_level' => 3,
];
$pool = Json::decode((string) file_get_contents(
    APP_ROOT . '/hwe/sammo/GeneralPool/Pool/UnderS100.json'
));
$columns = array_flip($pool['columns']);
$targets = [];
foreach ($pool['data'] as $idx => $row) {
    $name = $row[$columns['generalName']];
    if (!in_array($name, TARGET_NAMES, true)) {
        continue;
    }
    $info = array_combine($pool['columns'], $row);
    $info['uniqueName'] = sprintf('A100%04d', $idx + 1);
    $info['event100Growth'] = true;
    $targets[$name] = $info;
}
if (count($targets) !== count(TARGET_NAMES)) {
    throw new RuntimeException('Required S100 candidates are missing');
}

$db = DB::db();
$results = [];
foreach (TARGET_NAMES as $name) {
    $target = $targets[$name];
    $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
        's100-generated-npc-stat-floor-check',
        $target['uniqueName']
    )));
    $poolGeneral = new SPoolUnderU100(
        $db,
        $rng,
        $target,
        '2999-01-01 00:00:00'
    );
    $builder = $poolGeneral->getGeneralBuilder();
    $builder->setNationID(0)
        ->setNPCType(3)
        ->setMoney(1000, 1000)
        ->setExpDed(0, 0)
        ->setLifeSpan(160, 230);
    $builder->fillRandomStat(['무' => 0.333, '지' => 0.333, '무지' => 0.334]);
    $generatedStats = $builder->getStat();
    assertSameValue(
        GameConst::$defaultStatNPCTotal,
        array_sum($generatedStats),
        "{$name} generic total"
    );
    $builder->fillRemainSpecAsZero($env);
    $builder->build($env);
    CentennialAllStarGrowthService::applyCurrentTargetToBuiltNPC(
        $db,
        $builder,
        $target,
        $env
    );

    $general = General::createObjFromDB($builder->getGeneralID());
    $actualStats = [
        'leadership' => (int) $general->getVar('leadership'),
        'strength' => (int) $general->getVar('strength'),
        'intel' => (int) $general->getVar('intel'),
    ];
    if (array_sum($actualStats) < GameConst::$defaultStatNPCTotal) {
        throw new RuntimeException(
            "{$name} total fell below " . GameConst::$defaultStatNPCTotal
        );
    }

    $targetOrder = ['leadership', 'strength', 'intel'];
    usort(
        $targetOrder,
        static fn (string $lhs, string $rhs): int =>
            (int) $target[$rhs] <=> (int) $target[$lhs]
    );
    if ($actualStats[$targetOrder[0]] < $actualStats[$targetOrder[1]]
        || $actualStats[$targetOrder[1]] < $actualStats[$targetOrder[2]]
    ) {
        throw new RuntimeException("{$name} target stat order was not preserved");
    }

    $results[] = [
        'name' => $name,
        'target' => array_intersect_key($target, $actualStats),
        'generic' => array_combine(
            ['leadership', 'strength', 'intel'],
            $generatedStats
        ),
        'actual' => $actualStats,
        'total' => array_sum($actualStats),
    ];
}

echo Json::encode($results, Json::PRETTY), PHP_EOL;

function assertSameValue(int $expected, int $actual, string $label): void
{
    if ($actual !== $expected) {
        throw new RuntimeException(
            "{$label}: expected {$expected}, got {$actual}"
        );
    }
}
