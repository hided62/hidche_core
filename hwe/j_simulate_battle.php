<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin([])->setReadOnly();
$userID = Session::getUserID();

$query = Util::getReq('query');
if($query === null){
    Json::die([
        'result'=>false,
        'reason'=>'입력값이 없습니다.'
    ]);
}

$action = Util::getReq('action');
if($action === null || !in_array($action, ['reorder', 'battle'])){
    Json::die([
        'result'=>false,
        'reason'=>'원하는 동작이 지정되지 않았습니다.'
    ]);
}

$query = Json::decode($query);
if($query === null){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 JSON입니다.'
    ]);
}


$defaultCheck = [
    'required'=>[
        'attackerGeneral', 'attackerCity', 'attackerNation',
        'defenderGenerals', 'defenderCity', 'defenderNation',
        'year', 'month', 'repeatCnt'
    ],
    'integer'=>[
        'year','month','repeatCnt'
    ],
    'between'=>[
        ['month', [1, 12]]
    ],
    'in'=>[
        ['repeatCnt', [1, 1000]]
    ],
    'min'=>[
        ['year', 0]
    ],
    'array'=>[
        'attackerGeneral', 'attackerCity', 'attackerNation', 
        'defenderGenerals', 'defenderCity', 'defenderNation'
    ],
];

$v = new Validator($query);
$v->rules($defaultCheck);
if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>$v->errorStr()
    ]);
}

$year = $query['year'];
$month = $query['month'];
$repeatCnt = $query['repeatCnt'];

$rawAttacker = $query['attackerGeneral'];
$rawAttacker['turntime'] = date('Y-m-d H:i:s');
$rawAttackerCity = $query['attackerCity'];
$rawAttackerNation = $query['attackerNation'];

$defenderList = $query['defenderGenerals'];
$rawDefenderCity = $query['defenderCity'];
$rawDefenderNation = $query['defenderNation'];


$generalCheck = [
    'required'=>[
        'no', 'name', 'nation', 'turntime', 'personal', 'special2', 'crew', 'crewtype', 'atmos', 'train', 
        'intel', 'intel2', 'book', 'power', 'power2', 'weap', 'injury', 'leader', 'leader2', 'horse', 'item', 
        'explevel', 'experience', 'dedication', 'level', 'gold', 'rice', 'dex0', 'dex10', 'dex20', 'dex30', 'dex40',
        'warnum', 'killnum', 'deathnum', 'killcrew', 'deathcrew', 'recwar'
    ],
    'integer'=>[
        'no', 'nation', 'personal', 'special2', 'crew', 'crewtype', 'atmos', 'train',
        'intel', 'intel2', 'book', 'power', 'power2', 'weap', 'injury', 'leader', 'leader2', 'horse', 'item',
        'explevel', 'experience', 'dedication', 'level', 'gold', 'rice', 'dex0', 'dex10', 'dex20', 'dex30', 'dex40',
        'warnum', 'killnum', 'deathnum', 'killcrew', 'deathcrew'
    ],
    'min'=>[
        ['no', 1],
        ['nation', 1],
        ['crew', 0],
        ['intel', 0],
        ['power', 0],
        ['leader', 0],
        ['experience', 0],
        ['gold', 0],
        ['rice', 0],
        ['dex0', 0],
        ['dex10', 0],
        ['dex20', 0],
        ['dex30', 0],
        ['dex40', 0],
    ],
    'between'=>[
        ['train', [40, GameConst::$maxTrainByWar]],
        ['atmos', [40, GameConst::$maxAtmosByWar]],
        ['book', [0, 26]],
        ['weap', [0, 26]],
        ['horse', [0, 26]],
        ['item', [0, 26]],
        ['explevel', [0, 300]],
        ['injury', [0, 80]],
        ['level', [1, 12]]
    ],
    'in'=>[
        ['personal', array_keys(getCharacterList())],
        ['special2', array_merge(array_keys(SpecialityConst::WAR), [0])],
        ['crewtype', array_keys(GameUnitConst::all())],
    ]
];

$v = new Validator($rawAttacker);
$v->rules($generalCheck);
if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>'[출병자]'.$v->errorStr()
    ]);
}

foreach($defenderList as $idx=>$rawDefenderGeneral){
    $v = new Validator($rawDefenderGeneral);
    $v->rules($generalCheck);
    if(!$v->validate()){
        $idx+=1;
        Json::die([
            'result'=>false,
            'reason'=>"[수비자{$idx}]".$v->errorStr()
        ]);
    }
}


$cityCheck = [
    'required'=>[
        'city', 'nation', 'supply', 'name', 
        'pop', 'agri', 'comm', 'secu', 'def', 'wall', 
        'rate', 'level',
        'pop2', 'agri2', 'comm2', 'secu2', 'def2', 'wall2',
        'dead', 'state', 'gen1', 'gen2', 'gen3', 'conflict', 
    ],
    'numeric'=>[
        'pop', 'agri', 'comm', 'secu', 'def', 'wall', 'rate', 'dead'
    ],
    'integer'=>[
        'city', 'nation', 'supply',
        'pop2', 'agri2', 'comm2', 'secu2', 'def2', 'wall2',
        'state', 'gen1', 'gen2', 'gen3'
    ],
    'min'=>[
        ['def', 0],
        ['wall', 0],
        ['rate', 0],
        ['pop', 0],
        ['comm', 0],
        ['secu', 0],
        ['city', 1],
        ['nation', 0]
    ],
    'in'=>[
        ['level', array_keys(getCityLevelList())]
    ]
];

$v = new Validator($rawAttackerCity);
$v->rules($cityCheck);
if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>'[출병도시]'.$v->errorStr()
    ]);
}

$v = new Validator($rawDefenderCity);
$v->rules($cityCheck);
if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>'[수비도시]'.$v->errorStr()
    ]);
}

$nationCheck = [
    'required'=>[
        'type', 'tech', 'level', 'capital',
        'nation', 'name', 'gold', 'rice', 'totaltech', 'gennum'
    ],
    'integer'=>[
        'type', 'level', 'capital', 'nation', 'gennum',
    ],
    'numeric'=>[
        'tech', 'gold', 'rice', 'totaltech'
    ],
    'min'=>[
        ['tech', 0],
        ['totaltech', 0],
        ['gold', 0],
        ['rice', 0],
        ['gennum', 1],
        ['gen1', 0],
        ['gen2', 0],
        ['gen3', 0],
    ],
    'in'=>[
        ['type', array_keys(getNationTypeList())],
        ['level', array_keys(getNationLevelList())]
    ]
];

$v = new Validator($rawAttackerNation);
$v->rules($nationCheck);
if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>'[출병국]'.$v->errorStr()
    ]);
}

$v = new Validator($rawDefenderNation);
$v->rules($nationCheck);
if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>'[수비국]'.$v->errorStr()
    ]);
}

if($action == 'reorder'){
    usort($defenderList, function($lhs, $rhs){
        return -(extractBattleOrder($lhs) <=> extractBattleOrder($rhs));
    });

    $order = [];
    foreach($defenderList as $rawDefenderGeneral){
        $order[] = $rawDefenderGeneral['no'];
    }
    
    Json::die([
        'result'=>true,
        'reason'=>'success',
        'order'=>$order
    ]);
}

usort($defenderList, function($lhs, $rhs){
    return -(extractBattleOrder($lhs) <=> extractBattleOrder($rhs));
});

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$startYear = $gameStor->startyear;
$cityRate = Util::round(($year - $startYear) / 1.5) + 60;


function simulateBattle(
    $rawAttacker, $rawAttackerCity, $rawAttackerNation, 
    $defenderList, $rawDefenderCity, $rawDefenderNation, 
    $startYear, $year, $month, $cityRate
){
    $attacker = new WarUnitGeneral($rawAttacker, $rawAttackerCity, $rawAttackerNation, true, $year, $month);
    $city = new WarUnitCity($rawDefenderCity, $rawDefenderNation, $year, $month, $cityRate);

    $iterDefender = new \ArrayIterator($defenderList);
    $iterDefender->rewind();

    $battleResult = [];

    $attackerRice = $rawAttacker['rice'];
    $defenderRice = 0;

    $getNextDefender = function(?WarUnit $prevDefender, bool $reqNext) 
        use ($iterDefender, $rawDefenderCity, $rawDefenderNation, $year, $month, &$battleResult, &$defenderRice) {
        if($prevDefender !== null){
            $prevDefender->getLogger()->rollback();
            $battleResult[] = $prevDefender;
            if($prevDefender instanceof WarUnitGeneral){
                $defenderRice -= $prevDefender->getVar('rice');
            }
        }

        if(!$reqNext){
            return null;
        }

        if(!$iterDefender->valid()){
            return null;
        }

        $rawGeneral = $iterDefender->current();
        if(extractBattleOrder($rawGeneral) <= 0){
            return null;
        }

        $defenderRice += $rawGeneral['rice'];

        $retVal = new WarUnitGeneral($rawGeneral, $rawDefenderCity, $rawDefenderNation, false, $year, $month);
        $iterDefender->next();
        return $retVal;
    };

    $conquerCity = processWar_NG($attacker, $getNextDefender, $city, $year - $startYear);

    $rawDefenderCity = $city->getRaw();
    $updateAttackerNation = [];
    $updateDefenderNation = [];

    $attackerRice -= $attacker->getVar('rice');

    if($city->getPhase() > 0){
        $rice = $city->getKilled() / 100 * 0.8;
        $rice *= $city->getCrewType()->rice;
        $rice *= getTechCost($rawDefenderNation['tech']);
        $rice *= $cityRate / 100 - 0.2;
        Util::setRound($rice);

        $defenderRice += $rice;
    }

    $totalDead = $attacker->getKilled() + $attacker->getDead();
    $attackerCityDead = $totalDead * 0.4;
    $defenderCityDead = $totalDead * 0.6;

    return [$attacker, $city, $battleResult, $conquerCity, $attackerRice, $defenderRice];
}

$lastWarLog = [];

$attackerKilled = 0;
$attackerDead = 0;

$attackerAvgRice = 0;
$defenderAvgRice = 0;

$avgPhase = 0;
$avgWar = 0;

$attackerActivatedSkills = [];
$defendersActivatedSkills = [];

foreach(range(1, $repeatCnt) as $repeatIdx){
    [$attacker, $city, $battleResult, $conquerCity, $attackerRice, $defenderRice] = simulateBattle(
        $rawAttacker, $rawAttackerCity, $rawAttackerNation, 
        $defenderList, $rawDefenderCity, $rawDefenderNation, 
        $startYear, $year, $month, $cityRate
    );
    $lastWarLog = Util::mapWithKey(function($key, $values){
        return ConvertLog(join('<br>', $values));
    }, $attacker->getLogger()->rollback()); 

    $avgPhase += $attacker->getPhase() / $repeatCnt;

    $attackerKilled += $attacker->getKilled() / $repeatCnt;
    $attackerDead += $attacker->getDead() / $repeatCnt;

    $attackerAvgRice += $attackerRice / $repeatCnt;
    $defenderAvgRice += $defenderRice / $repeatCnt;

    $avgWar += count($battleResult) / $repeatCnt;

    foreach($attacker->getActivatedSkillLog() as $skillName => $skillCnt){
        if(!key_exists($skillName, $attackerActivatedSkills)){
            $attackerActivatedSkills[$skillName] = $skillCnt / $repeatCnt;
        }
        else{
            $attackerActivatedSkills[$skillName] += $skillCnt / $repeatCnt;
        }
    }

    foreach($battleResult as $idx=>$defender){
        while($idx >= count($defendersActivatedSkills)){
            $defendersActivatedSkills[] = [];
        }

        $activatedSkills = &$defendersActivatedSkills[$idx];
        foreach($defender->getActivatedSkillLog() as $skillName => $skillCnt){
            if(!key_exists($skillName, $activatedSkills)){
                $activatedSkills[$skillName] = $skillCnt / $repeatCnt;
            }
            else{
                $activatedSkills[$skillName] += $skillCnt / $repeatCnt;
            }
        }
        
    }
}

Json::die([
    'result'=>true,
    'datetime'=>$rawAttacker['turntime'],
    'reason'=>'success',
    'lastWarLog'=>$lastWarLog,
    'avgWar'=>$avgWar,
    'phase'=>$avgPhase,
    'killed'=>$attackerKilled,
    'dead'=>$attackerDead,
    'attackerRice'=>$attackerAvgRice,
    'defenderRice'=>$defenderAvgRice,
    'attackerSkills'=>$attackerActivatedSkills,
    'defendersSkills'=>$defendersActivatedSkills,
]);