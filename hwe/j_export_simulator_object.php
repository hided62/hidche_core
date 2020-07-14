<?php

namespace sammo;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();

$destGeneralID = Util::getPost('destGeneralID', 'int', 0);

if(!$destGeneralID || $destGeneralID <= 0){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 장수 코드입니다'
    ]);
}

$session = Session::requireGameLogin([])->setReadOnly();

$db = DB::db();

increaseRefresh("시뮬레이터 추출", 1);

$userID = Session::getUserID();
$me = $db->queryFirstRow('SELECT no,nation from general where owner=%i', $userID);
$nationID = $me['nation'];

$reqColumns = [
    'nation',
    'name', 'officer_level', 'explevel', 'injury', 
    'leadership', 'horse', 'strength', 'weapon', 'intel', 'book', 'item', 
    'rice', 'personal', 'special2', 
    'crew', 'crewtype', 
    'atmos', 'train', 'dex1', 'dex2', 'dex3', 'dex4', 'dex5', 'defence_train',
];

$reqRankColumns = [
    'warnum', 'killnum', 'killcrew'
];

$dummyItems = [
    'officer_level'=>1,
    'horse'=>'None',
    'weapon'=>'None',
    'book'=>'None',
    'item'=>'None',
    'crew'=>0,
    'crewtype'=>GameUnitConst::DEFAULT_CREWTYPE,
    'rice'=>10000,
    'train'=>GameConst::$maxTrainByCommand,
    'atmos'=>GameConst::$maxTrainByCommand,
    'dex1'=>0,
    'dex2'=>0,
    'dex3'=>0,
    'dex4'=>0,
    'dex5'=>0,
    'defence_train'=>80,
    'warnum'=>0,
    'killnum'=>0,
    'killcrew'=>0,
];

$rawDestGeneral = $db->queryFirstRow('SELECT %l FROM general WHERE no=%i', Util::formatListOfBackticks($reqColumns), $destGeneralID);

if($nationID == 0 || $rawDestGeneral['nation'] != $nationID){
    foreach($dummyItems as $key=>$val){
        $rawDestGeneral[$key] = $val;
    }
}
else{
    $rawRankValue = $db->queryAllLists('SELECT `type`, `value` FROM rank_data WHERE general_id = %i AND `type` IN %ls', $destGeneralID, $reqRankColumns);
    foreach($rawRankValue as [$rankType, $rankValue]){
        $rawDestGeneral[$rankType] = $rankValue;
    }
}

foreach(['dex1', 'dex2', 'dex3', 'dex4', 'dex5'] as $dexKey){
    $dex = $rawDestGeneral[$dexKey];
    $rawDestGeneral[$dexKey] = getDexLevelList()[getDexLevel($dex)][0];
}

Json::die([
    'result'=>true,
    'reason'=>'success',
    'general'=>$rawDestGeneral,
]);