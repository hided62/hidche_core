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
    'array'=>['attackerGeneral', 'attackerCity', 'attackerNation', 'defenderGenerals', 'defenderCity', 'defenderNation'],
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

$generalCheck = [
    'required'=>[
        'no', 'name', 'nation', 'turntime', 'personal', 'special2', 'crew', 'crewtype', 'atmos', 'train', 
        'intel', 'intel2', 'book', 'power', 'power2', 'weap', 'injury', 'leader', 'leader2', 'horse', 'item', 
        'explevel', 'experience', 'dedication', 'level', 'gold', 'rice', 'dex0', 'dex10', 'dex20', 'dex30', 'dex40',
        'warnum', 'killnum', 'deathnum', 'killcrew', 'deathcrew', 'recwar'
    ]
];
$v = new Validator($query['attackerGeneral']);






Json::die([
    'result'=>true,
    'reason'=>'NYI'
]);
