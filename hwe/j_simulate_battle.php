<?php

namespace sammo;

use Ds\Map;
use sammo\Enums\RankColumn;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();

$session = Session::requireLogin([])->setReadOnly();
$userID = Session::getUserID();

increaseRefresh("시뮬레이터", 0);

$query = Util::getPost('query');
if ($query === null) {
    Json::die([
        'result' => false,
        'reason' => '입력값이 없습니다.'
    ]);
}

$action = Util::getPost('action');
if ($action === null || !in_array($action, ['reorder', 'battle'])) {
    Json::die([
        'result' => false,
        'reason' => '원하는 동작이 지정되지 않았습니다.'
    ]);
}

$query = Json::decode($query);
if ($query === null) {
    Json::die([
        'result' => false,
        'reason' => '올바르지 않은 JSON입니다.'
    ]);
}

$defaultCheck = [
    'required' => [
        'attackerGeneral', 'attackerCity', 'attackerNation',
        'defenderGenerals', 'defenderCity', 'defenderNation',
        'year', 'month', 'repeatCnt'
    ],
    'integer' => [
        'year', 'month', 'repeatCnt'
    ],
    'between' => [
        ['month', [1, 12]]
    ],
    'in' => [
        ['repeatCnt', [1, 1000]]
    ],
    'min' => [
        ['year', 0]
    ],
    'array' => [
        'attackerGeneral', 'attackerCity', 'attackerNation',
        'defenderGenerals', 'defenderCity', 'defenderNation'
    ],
];

$v = new Validator($query);
$v->rules($defaultCheck);
if (!$v->validate()) {
    Json::die([
        'result' => false,
        'reason' => $v->errorStr()
    ]);
}

$year = $query['year'];
$month = $query['month'];
$repeatCnt = $query['repeatCnt'];

$rawAttacker = $query['attackerGeneral'];
$rawAttacker['turntime'] = TimeUtil::now();
$rawAttackerCity = $query['attackerCity'];
$rawAttackerNation = $query['attackerNation'];

$rawDefenderList = $query['defenderGenerals'];
$rawDefenderCity = $query['defenderCity'];
$rawDefenderNation = $query['defenderNation'];

$warSeed = $query['seed'] ?? '';

$cityCheck = [
    'required' => [
        'city', 'nation', 'supply', 'name',
        'pop', 'agri', 'comm', 'secu', 'def', 'wall',
        'trust', 'level',
        'pop_max', 'agri_max', 'comm_max', 'secu_max', 'def_max', 'wall_max',
        'dead', 'state', 'conflict',
    ],
    'numeric' => [
        'pop', 'agri', 'comm', 'secu', 'def', 'wall', 'trust', 'dead'
    ],
    'integer' => [
        'city', 'nation', 'supply',
        'pop_max', 'agri_max', 'comm_max', 'secu_max', 'def_max', 'wall_max',
        'state',
    ],
    'min' => [
        ['def', 0],
        ['wall', 0],
        ['trust', 0],
        ['pop', 0],
        ['comm', 0],
        ['secu', 0],
        ['city', 1],
        ['nation', 0]
    ],
    'in' => [
        ['level', array_keys(getCityLevelList())]
    ]
];

$v = new Validator($rawAttackerCity);
$v->rules($cityCheck);
if (!$v->validate()) {
    Json::die([
        'result' => false,
        'reason' => '[출병도시]' . $v->errorStr()
    ]);
}

$v = new Validator($rawDefenderCity);
$v->rules($cityCheck);
if (!$v->validate()) {
    Json::die([
        'result' => false,
        'reason' => '[수비도시]' . $v->errorStr()
    ]);
}

$nationCheck = [
    'required' => [
        'type', 'tech', 'level', 'capital',
        'nation', 'name', 'gold', 'rice', 'gennum'
    ],
    'integer' => [
        'level', 'capital', 'nation', 'gennum',
    ],
    'numeric' => [
        'tech', 'gold', 'rice'
    ],
    'min' => [
        ['tech', 0],
        ['gold', 0],
        ['rice', 0],
        ['gennum', 1],
    ],
    'in' => [
        ['type', GameConst::$availableNationType],
        ['level', array_keys(getNationLevelList())]
    ]
];

$v = new Validator($rawAttackerNation);
$v->rules($nationCheck);
if (!$v->validate()) {
    Json::die([
        'result' => false,
        'reason' => '[출병국]' . $v->errorStr()
    ]);
}

$v = new Validator($rawDefenderNation);
$v->rules($nationCheck);
if (!$v->validate()) {
    Json::die([
        'result' => false,
        'reason' => '[수비국]' . $v->errorStr()
    ]);
}

$generalCheck = [
    'required' => [
        'no', 'name', 'nation', 'turntime', 'personal', 'special2', 'crew', 'crewtype', 'atmos', 'train',
        'intel', 'intel_exp', 'book', 'strength', 'strength_exp', 'weapon', 'injury', 'leadership', 'leadership_exp', 'horse', 'item',
        'explevel', 'experience', 'dedication', 'officer_level', 'officer_city', 'gold', 'rice', 'dex1', 'dex2', 'dex3', 'dex4', 'dex5',
        'recent_war', 'warnum', 'killnum', 'killcrew',
    ],
    'integer' => [
        'no', 'nation', 'crew', 'crewtype', 'atmos', 'train',
        'intel', 'intel_exp', 'strength', 'strength_exp',  'injury', 'leadership', 'leadership_exp',
        'explevel', 'experience', 'dedication', 'officer_level', 'officer_city', 'gold', 'rice', 'dex1', 'dex2', 'dex3', 'dex4', 'dex5',
        'warnum', 'killnum', 'killcrew',
    ],
    'min' => [
        ['no', 1],
        ['nation', 1],
        ['crew', 0],
        ['intel', 0],
        ['strength', 0],
        ['leadership', 0],
        ['experience', 0],
        ['gold', 0],
        ['rice', 0],
        ['dex1', 0],
        ['dex2', 0],
        ['dex3', 0],
        ['dex4', 0],
        ['dex5', 0],
        ['warnum', 0],
        ['killnum', 0],
        ['killcrew', 0],
    ],
    'between' => [
        ['train', [40, GameConst::$maxTrainByWar]],
        ['atmos', [40, GameConst::$maxAtmosByWar]],
        ['explevel', [0, 300]],
        ['injury', [0, 80]],
        ['officer_level', [1, 12]]
    ],
    'in' => [
        ['personal', array_merge(GameConst::$availablePersonality, GameConst::$optionalPersonality)],
        ['special2', array_merge(GameConst::$availableSpecialWar, GameConst::$optionalSpecialWar)],
        ['crewtype', array_keys(GameUnitConst::all())],
        ['horse', array_merge(array_keys(GameConst::$allItems['horse']), ['None'])],
        ['weapon', array_merge(array_keys(GameConst::$allItems['weapon']), ['None'])],
        ['book', array_merge(array_keys(GameConst::$allItems['book']), ['None'])],
        ['item', array_merge(array_keys(GameConst::$allItems['item']), ['None'])],
    ],
    'array' => ['inheritBuff'],
];

$inheritBuffCheck = [
    'between' => [
        [TriggerInheritBuff::WAR_AVOID_RATIO, [0, 5]],
        [TriggerInheritBuff::WAR_CRITICAL_RATIO, [0, 5]],
        [TriggerInheritBuff::WAR_MAGIC_TRIAL_PROB, [0, 5]],

        [TriggerInheritBuff::OPPOSE_WAR_AVOID_RATIO, [0, 5]],
        [TriggerInheritBuff::OPPOSE_WAR_CRITICAL_RATIO, [0, 5]],
        [TriggerInheritBuff::OPPOSE_WAR_MAGIC_TRIAL_PROB, [0, 5]],
    ]
];

if(!$warSeed){
    $warSeed = bin2hex(random_bytes(16));
}
else {
    $repeatCnt = 1;
}
$tmpRNG = new RandUtil(new LiteHashDRBG($warSeed));

$v = new Validator($rawAttacker);
$v->rules($generalCheck);
if (!$v->validate()) {
    Json::die([
        'result' => false,
        'reason' => '[출병자]' . $v->errorStr()
    ]);
}
$rawAttacker['aux'] = [];
if (key_exists('inheritBuff', $rawAttacker)) {
    $v = new Validator($rawAttacker['inheritBuff']);
    $v->rules($inheritBuffCheck);
    if (!$v->validate()) {
        Json::die([
            'result' => false,
            'reason' => '[출병자]' . $v->errorStr()
        ]);
    }
    $rawAttacker['aux']['inheritBuff'] = $rawAttacker['inheritBuff'];
}
$rawAttacker['aux'] = Json::encode($rawAttacker['aux']);
$rawAttacker['owner'] = 0;
$attackerGeneral = new General($rawAttacker, extractRankVar($rawAttacker), $rawAttackerCity, $rawAttackerNation, $year, $month);
$attacker = new WarUnitGeneral(
    $tmpRNG,
    $attackerGeneral,
    $rawAttackerNation,
    true
);


/** @var WarUnit[] */
$defenderList = [];

foreach ($rawDefenderList as $idx => $rawDefenderGeneral) {
    $v = new Validator($rawDefenderGeneral);
    $v->rules($generalCheck);
    if (!$v->validate()) {
        $idx += 1;
        Json::die([
            'result' => false,
            'reason' => "[수비자{$idx}]" . $v->errorStr()
        ]);
    }
    $rawDefenderGeneral['aux'] = [];
    if (key_exists('inheritBuff', $rawDefenderGeneral)) {
        $v = new Validator($rawDefenderGeneral['inheritBuff']);
        $v->rules($inheritBuffCheck);
        if (!$v->validate()) {
            $idx += 1;
            Json::die([
                'result' => false,
                'reason' => "[수비자{$idx}]" . $v->errorStr()
            ]);
        }
        $rawDefenderGeneral['aux']['inheritBuff'] = $rawDefenderGeneral['inheritBuff'];
    }
    $rawDefenderGeneral['aux'] = Json::encode($rawDefenderGeneral['aux']);
    $rawDefenderGeneral['owner'] = 0;

    $defenderGeneral = new General($rawDefenderGeneral, extractRankVar($rawDefenderGeneral), $rawDefenderCity, $rawAttackerNation, $year, $month, true);

    $defenderList[] = new WarUnitGeneral(
        $tmpRNG,
        $defenderGeneral,
        $rawDefenderNation,
        false
    );
}

if ($action == 'reorder') {
    $noRng = NoRNG::rngInstance();

    usort($defenderList, function (WarUnit $lhs, WarUnit $rhs) use ($attacker) {
        return - (extractBattleOrder($lhs, $attacker) <=> extractBattleOrder($rhs, $attacker));
    });

    $order = [];
    foreach ($defenderList as $defender) {
        $order[] = $defender->getGeneral()->getID();
    }

    Json::die([
        'result' => true,
        'reason' => 'success',
        'order' => $order
    ]);
}

$rawDefenderList = [];
foreach($defenderList as $unit){
    if(extractBattleOrder($unit, $attacker) <= 0){
        continue;
    }
    $rawDefenderList[] = $unit->getRaw();
}
unset($defenderList);

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$startYear = $gameStor->startyear;

function extractRankVar(array $rawVal): Map
{
    $rankVars = new Map;
    foreach ($rawVal as $rawKey => $rawVal) {
        $key = RankColumn::tryFrom($rawKey);
        if ($key === null) {
            continue;
        }
        $rankVars[$key] = $rawVal;
    }
    return $rankVars;
}

function simulateBattle(
    $rawAttacker,
    $rawAttackerCity,
    $rawAttackerNation,
    $rawDefenderList,
    $rawDefenderCity,
    $rawDefenderNation,
    $startYear,
    $year,
    $month,
    $warSeed
) {

    if (!$warSeed) {
        $warSeed = bin2hex(random_bytes(16));
    }

    $warRng = new RandUtil(new LiteHashDRBG($warSeed));

    $attackerGeneral = new General($rawAttacker, extractRankVar($rawAttacker), $rawAttackerCity, $rawAttackerNation, $year, $month);
    $attacker = new WarUnitGeneral(
        $warRng,
        $attackerGeneral,
        $rawAttackerNation,
        true
    );
    $city = new WarUnitCity($warRng, $rawDefenderCity, $rawDefenderNation, $year, $month, $startYear);

    /** @var WarUnit[] */
    $defenderList = [];

    foreach ($rawDefenderList as $rawDefenderGeneral) {
        $defenderGeneral = new General($rawDefenderGeneral, extractRankVar($rawDefenderGeneral), $rawDefenderCity, $rawAttackerNation, $year, $month, true);

        $defenderList[] = new WarUnitGeneral(
            $warRng,
            $defenderGeneral,
            $rawDefenderNation,
            false
        );
    }

    if(count($defenderList) && extractBattleOrder($city, $attacker) > 0){
        $defenderList[] = $city;
    }

    usort($defenderList, function (WarUnit $lhs, WarUnit $rhs) use ($attacker) {
        return - (extractBattleOrder($lhs, $attacker) <=> extractBattleOrder($rhs, $attacker));
    });

    $iterDefender = new \ArrayIterator($defenderList);
    $iterDefender->rewind();

    $battleResult = [];

    $attackerRice = $attacker->getGeneral()->getVar('rice');
    $defenderRice = 0;

    $getNextDefender = function (?WarUnit $prevDefender, bool $reqNext)
    use ($iterDefender, &$battleResult, &$defenderRice, $attacker): ?WarUnit {
        if ($prevDefender !== null) {
            $prevDefender->getLogger()->rollback();
            $battleResult[] = $prevDefender;
            if ($prevDefender instanceof WarUnitGeneral) {
                $defenderRice -= $prevDefender->getVar('rice');
            }
        }

        if (!$reqNext) {
            return null;
        }

        if (!$iterDefender->valid()) {
            return null;
        }

        /** @var WarUnit */
        $defender = $iterDefender->current();

        if (extractBattleOrder($defender, $attacker) <= 0) {
            return null;
        }

        if($defender instanceof WarUnitGeneral){
            $defenderRice += $defender->getVar('rice');
        }

        $iterDefender->next();
        return $defender;
    };

    $conquerCity = processWar_NG($warSeed, $attacker, $getNextDefender, $city);

    $attackerRice -= $attacker->getVar('rice');

    if ($city->getPhase() > 0) {
        $rice = $city->getKilled() / 100 * 0.8;
        $rice *= $city->getCrewType()->rice;
        $rice *= getTechCost($city->getRawNation()['tech']);
        $rice *= $city->getCityTrainAtmos() / 100 - 0.2;
        Util::setRound($rice);

        $defenderRice += $rice;
    }

    $totalDead = $attacker->getKilled() + $attacker->getDead();
    //$attackerCityDead = $totalDead * 0.4;
    //$defenderCityDead = $totalDead * 0.6;

    return [$attacker, $city, $battleResult, $conquerCity, $attackerRice, $defenderRice];
}

$lastWarLog = [];

$attackerKilled = 0;
$attackerDead = 0;

$attackerMaxKilled = 0;
$attackerMinKilled = PHP_INT_MAX;

$attackerMaxDead = 0;
$attackerMinDead = PHP_INT_MAX;


$attackerAvgRice = 0;
$defenderAvgRice = 0;

$avgPhase = 0;
$avgWar = 0;

$attackerActivatedSkills = [];
$defendersActivatedSkills = [];


foreach (Util::range($repeatCnt) as $repeatIdx) {
    if($repeatIdx > 0){
        $warSeed = bin2hex(random_bytes(16));
    }

    /** @var WarUnit $attacker */
    [$attacker, $city, $battleResult, $conquerCity, $attackerRice, $defenderRice] = simulateBattle(
        $rawAttacker,
        $rawAttackerCity,
        $rawAttackerNation,
        $rawDefenderList,
        $rawDefenderCity,
        $rawDefenderNation,
        $startYear,
        $year,
        $month,
        $warSeed
    );
    $lastWarLog = Util::mapWithKey(function ($key, $values) {
        return ConvertLog(join('<br>', $values));
    }, $attacker->getLogger()->rollback());

    $avgPhase += $attacker->getPhase() / $repeatCnt;

    $killed = $attacker->getKilled();
    $dead = $attacker->getDead();

    $attackerKilled += $killed / $repeatCnt;
    $attackerDead += $dead / $repeatCnt;

    $attackerMaxKilled = max($attackerMaxKilled, $killed);
    $attackerMinKilled = min($attackerMinKilled, $killed);

    $attackerMaxDead = max($attackerMaxDead, $dead);
    $attackerMinDead = min($attackerMinDead, $dead);

    $attackerAvgRice += $attackerRice / $repeatCnt;
    $defenderAvgRice += $defenderRice / $repeatCnt;

    $avgWar += count($battleResult) / $repeatCnt;

    foreach ($attacker->getActivatedSkillLog() as $skillName => $skillCnt) {
        if (!key_exists($skillName, $attackerActivatedSkills)) {
            $attackerActivatedSkills[$skillName] = $skillCnt / $repeatCnt;
        } else {
            $attackerActivatedSkills[$skillName] += $skillCnt / $repeatCnt;
        }
    }

    foreach ($battleResult as $idx => $defender) {
        while ($idx >= count($defendersActivatedSkills)) {
            $defendersActivatedSkills[] = [];
        }

        $activatedSkills = &$defendersActivatedSkills[$idx];
        foreach ($defender->getActivatedSkillLog() as $skillName => $skillCnt) {
            if (!key_exists($skillName, $activatedSkills)) {
                $activatedSkills[$skillName] = $skillCnt / $repeatCnt;
            } else {
                $activatedSkills[$skillName] += $skillCnt / $repeatCnt;
            }
        }
    }
}

Json::die([
    'result' => true,
    'datetime' => $rawAttacker['turntime'],
    'reason' => 'success',
    'lastWarLog' => $lastWarLog,
    'avgWar' => $avgWar,
    'phase' => $avgPhase,
    'killed' => $attackerKilled,
    'maxKilled' => $attackerMaxKilled,
    'minKilled' => $attackerMinKilled,
    'dead' => $attackerDead,
    'maxDead' => $attackerMaxDead,
    'minDead' => $attackerMinDead,
    'attackerRice' => $attackerAvgRice,
    'defenderRice' => $defenderAvgRice,
    'attackerSkills' => $attackerActivatedSkills,
    'defendersSkills' => $defendersActivatedSkills,
]);
