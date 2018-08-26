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

$attackerGeneral = $query['attackerGeneral'];
$attackerCity = $query['attackerCity'];
$attackerNation = $query['attackerNation'];

$defenderGenerals = $query['defenderGenerals'];
$defenderCity = $query['defenderCity'];
$defenderNation = $query['defenderNation'];


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
        ['special2', array_keys(SpecialityConst::WAR)],
        ['crewtype', array_keys(GameUnitConst::all())],
    ]
];

$v = new Validator($attackerGeneral);
$v->rules($generalCheck);
if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>'[출병자]'.$v->errorStr()
    ]);
}

foreach($defenderGenerals as $idx=>$defenderGeneral){
    $v = new Validator($defenderGeneral);
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

$v = new Validator($attackerCity);
$v->rules($cityCheck);
if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>'[출병도시]'.$v->errorStr()
    ]);
}

$v = new Validator($defenderCity);
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

$v = new Validator($attackerNation);
$v->rules($nationCheck);
if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>'[출병국]'.$v->errorStr()
    ]);
}

$v = new Validator($defenderNation);
$v->rules($nationCheck);
if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>'[수비국]'.$v->errorStr()
    ]);
}

if($action == 'reorder'){
    usort($defenderGenerals, function($lhs, $rhs){
        return -(extractBattleOrder($lhs) <=> extractBattleOrder($rhs));
    });

    $order = [];
    foreach($defenderGenerals as $defenderGeneral){
        $order[] = $defenderGeneral['no'];
    }
    
    Json::die([
        'result'=>true,
        'reason'=>'success',
        'order'=>$order
    ]);
}

Json::die([
    'result'=>true,
    'reason'=>'NYI'
]);
