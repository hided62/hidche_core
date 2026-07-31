<?php

declare(strict_types=1);

use sammo\CentennialAllStarGrowthService;
use sammo\DB;
use sammo\Event\Action\AdvanceCentennialAllStar;
use sammo\GameConst;
use sammo\General;
use sammo\Json;
use sammo\KVStorage;
use sammo\RootDB;

const APP_ROOT = '/var/www/html';
const TEST_ENV = ['startyear' => 180, 'year' => 195, 'month' => 1];
const STAT_KEYS = ['leadership', 'strength', 'intel'];
const DEX_KEYS = ['dex1', 'dex2', 'dex3', 'dex4', 'dex5'];

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/s100-reselection-final-growth-check';

require APP_ROOT . '/hwe/lib.php';
require APP_ROOT . '/hwe/func.php';

$mode = $argv[1] ?? '';
$username = $argv[2] ?? 's100user01';
$snapshotPath = $argv[3] ?? '/tmp/s100-reselection-final-growth.json';
if (!in_array($mode, ['prepare', 'verify'], true)) {
    throw new InvalidArgumentException('Mode must be prepare or verify');
}
if (preg_match('/^s100user[0-9]{2}$/', $username) !== 1) {
    throw new InvalidArgumentException('Username must match s100userNN');
}

$db = DB::db();
$owner = RootDB::db()->queryFirstField(
    'SELECT no FROM member WHERE id=%s',
    $username
);
if ($owner === null) {
    throw new RuntimeException("No member for {$username}");
}
$generalID = $db->queryFirstField('SELECT no FROM general WHERE owner=%i', $owner);
if ($generalID === null) {
    throw new RuntimeException("No general for owner {$owner}");
}

if ($mode === 'prepare') {
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $gameStor->setValue('year', TEST_ENV['year']);
    $gameStor->setValue('month', TEST_ENV['month']);

    $eventResult = (new AdvanceCentennialAllStar())->run(TEST_ENV);
    $general = General::createObjFromDB((int) $generalID);
    $beforeGrowth = readGeneralState($general);

    $statGrowthKey = null;
    foreach (STAT_KEYS as $key) {
        if ((int) $general->getVar($key) < GameConst::$maxLevel) {
            $statGrowthKey = $key;
            break;
        }
    }
    if ($statGrowthKey === null) {
        throw new RuntimeException('No stat can receive an organic level-up');
    }
    $general->increaseVar(
        "{$statGrowthKey}_exp",
        GameConst::$upgradeLimit
    );
    if (!$general->checkStatChange()) {
        throw new RuntimeException('Organic stat level-up did not occur');
    }

    $general->addDex($general->getCrewTypeObj(), 12345, false);
    $general->setAuxVar('next_change', '2000-01-01 00:00:00');
    $general->applyDB($db);

    $afterGrowth = readGeneralState($general);
    $snapshot = [
        'username' => $username,
        'generalID' => (int) $generalID,
        'eventResult' => $eventResult,
        'statGrowthKey' => $statGrowthKey,
        'beforeGrowth' => $beforeGrowth,
        'afterGrowth' => $afterGrowth,
    ];
    if (file_put_contents(
        $snapshotPath,
        Json::encode($snapshot, Json::PRETTY) . PHP_EOL
    ) === false) {
        throw new RuntimeException("Could not write {$snapshotPath}");
    }
    chmod($snapshotPath, 0600);
    printf(
        "Prepared final-growth reselection: user=%s target=%s stat=%s dexDelta=%d\n",
        $username,
        $afterGrowth['targetId'],
        $statGrowthKey,
        array_sum($afterGrowth['dex']) - array_sum($beforeGrowth['dex'])
    );
    exit(0);
}

$snapshot = Json::decode((string) file_get_contents($snapshotPath));
$general = General::createObjFromDB((int) $generalID);
$actual = readGeneralState($general);
$targetInfoRaw = $db->queryFirstField(
    'SELECT info FROM select_pool WHERE general_id=%i',
    $generalID
);
if ($targetInfoRaw === null) {
    throw new RuntimeException('Reselected target is not assigned to the general');
}
$targetInfo = Json::decode($targetInfoRaw);
$oldTargetId = $snapshot['afterGrowth']['targetId'];
$newTargetId = (string) ($targetInfo['uniqueName'] ?? '');
if ($newTargetId === '' || $newTargetId === $oldTargetId) {
    throw new RuntimeException('Chromium reselection did not change the target');
}
if ($actual['targetId'] !== $newTargetId) {
    throw new RuntimeException('General aux target does not match the assigned pool target');
}

$expectedStats = CentennialAllStarGrowthService::calculateUserCurrentTargetStats(
    $targetInfo,
    TEST_ENV
);
$expectedDex = [];
foreach (STAT_KEYS as $key) {
    $organic = max(
        0,
        (int) $snapshot['afterGrowth']['stats'][$key]
            - (int) $snapshot['afterGrowth']['granted'][$key]
    );
    $expectedStats[$key] = max($organic, $expectedStats[$key]);
    assertSameValue($expectedStats[$key], $actual['stats'][$key], $key);
    assertSameValue(
        $actual['stats'][$key] - $organic,
        $actual['granted'][$key],
        "{$key} event grant"
    );
}
foreach (DEX_KEYS as $idx => $key) {
    $organic = max(
        0,
        (int) $snapshot['afterGrowth']['dex'][$key]
            - (int) $snapshot['afterGrowth']['granted'][$key]
    );
    $targetFloor = CentennialAllStarGrowthService::calculateDexTargetFloor(
        (int) ($targetInfo['dex'][$idx] ?? 0),
        TEST_ENV
    );
    $expectedDex[$key] = max($organic, $targetFloor);
    assertSameValue($expectedDex[$key], $actual['dex'][$key], $key);
    assertSameValue($targetFloor, $actual['dexFloor'][$key], "{$key} floor");
    assertSameValue(
        $actual['dex'][$key] - $organic,
        $actual['granted'][$key],
        "{$key} event grant"
    );
}

printf(
    "Verified final-growth reselection: user=%s old=%s new=%s stats=%s dex=%s\n",
    $username,
    $oldTargetId,
    $newTargetId,
    Json::encode($actual['stats']),
    Json::encode($actual['dex'])
);

/**
 * @return array{
 *   targetId:string,
 *   stats:array<string,int>,
 *   dex:array<string,int>,
 *   granted:array<string,int>,
 *   dexFloor:array<string,int>
 * }
 */
function readGeneralState(General $general): array
{
    $aux = $general->getAuxVar(CentennialAllStarGrowthService::AUX_KEY);
    if (!is_array($aux)) {
        throw new RuntimeException('Centennial growth ledger is missing');
    }
    $stats = [];
    foreach (STAT_KEYS as $key) {
        $stats[$key] = (int) $general->getVar($key);
    }
    $dex = [];
    foreach (DEX_KEYS as $key) {
        $dex[$key] = (int) $general->getVar($key);
    }
    return [
        'targetId' => (string) ($aux['targetId'] ?? ''),
        'stats' => $stats,
        'dex' => $dex,
        'granted' => $aux['granted'] ?? [],
        'dexFloor' => $aux['dexFloor'] ?? [],
    ];
}

function assertSameValue(int $expected, int $actual, string $label): void
{
    if ($actual !== $expected) {
        throw new RuntimeException(
            "{$label}: expected {$expected}, got {$actual}"
        );
    }
}
