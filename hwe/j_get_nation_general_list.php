<?php
namespace sammo;

//통합 세력장수&암행부 viewer

include "lib.php";
include "func.php";

//로그인 검사
$session = Session::requireGameLogin([])->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$reqType = Util::getPost('req', 'int', 1);
$reqForce = Util::getPost('force', 'bool', false);
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("세력장수", 1);

$me = $db->queryFirstRow('SELECT con, turntime, nation, officer_level, permission, penalty FROM general WHERE owner=%i', $userID);
$con = checkLimit($me['con']);
if ($con >= 2) {
    Json::die([
        'result'=>false,
        'reason'=>'접속 제한중입니다. 1턴 이내에 너무 많은 갱신을 하셨습니다.'
    ]);
}

$nationID = $me['nation'];
$permission = checkSecretPermission($me, true);
if($reqForce && $reqType > $permission){
    Json::die([
        'result'=>false,
        'reason'=>'권한이 부족합니다.'
    ]);
}
$permission = min($reqType, $permission);
$nationArr = getNationStaticInfo($nationID);
$viewColumns = [
    'no'=>1,
    'name'=>1,
    'owner_name'=>9,
    'nation'=>1,
    'city'=>1,
    'officer_level'=>9,
    'npc'=>1,
    'defence_train'=>2,
    'troop'=>2,
    'injury'=>1,
    'leadership'=>1,
    'strength'=>1,
    'intel'=>1,
    'experience'=>1,
    'explevel'=>1,
    'dedication'=>1,
    'dedlevel'=>1,
    'gold'=>1,
    'rice'=>1,
    'crewtype'=>2,
    'crew'=>2,
    'train'=>2,
    'atmos'=>2,
    'killturn'=>1,
    'turntime'=>2,
    'picture'=>1,
    'imgsvr'=>1,
    'age'=>1,
    'special'=>1,
    'special2'=>1,
    'personal'=>1,
    'horse'=>3,
    'weapon'=>3,
    'book'=>3,
    'item'=>3,
    'connect'=>1
];

$customViewColumns = [
    'officerLevel'=>1,
    'officerLevelText'=>1,
    'lbonus'=>1,
    'ownerName'=>1,
    'honorText'=>1,
    'expLevelText'=>1,
];

$specialViewFilter = [
    'officerLevel'=>function($rawGeneral){
        return $rawGeneral['officer_level'];
    },
    'special'=>function($rawGeneral){
        return getGeneralSpecialDomesticName($rawGeneral['special']);
    },
    'special2'=>function($rawGeneral){
        return getGeneralSpecialWarName($rawGeneral['special2']);
    },
    'personal'=>function($rawGeneral){
        return getGenChar($rawGeneral['personal']);
    },
    'lbonus'=>function($rawGeneral)use($nationArr){
        return calcLeadershipBonus($rawGeneral['officer_level'], $nationArr['level']);
    },
    'ownerName'=>function($rawGeneral){
        if($rawGeneral['npc']!=1){
            return null;
        }
        return $rawGeneral['owner_name'];
    },
    'officerLevelText'=>function($rawGeneral)use($nationArr){
        return getOfficerLevelText($rawGeneral['officer_level'], $nationArr['level']);
    },
    'honorText'=>function($rawGeneral){
        return getHonor($rawGeneral['experience']);
    },
    'dedLevelText'=>function($rawGeneral){
        return getDedLevelText($rawGeneral['dedLevel']);
    },
    'turntime'=>function($rawGeneral){
        //'0000-00-00 11:23';
        return substr($rawGeneral['turntime'], 11, 5);
    }

];

$queryColumns = General::mergeQueryColumn(array_keys($viewColumns), 1);

$rawGeneralList = Util::convertArrayToDict($db->queryAllLists('SELECT %lb from general WHERE nation = %i', $queryColumns, $nationID), 'no');

$resultColumns = [];
foreach($viewColumns as $column=>$reqPermission){
    if($reqPermission > $permission){
        continue;
    }
    $resultColumns[] = $column;
}

foreach($customViewColumns as $column=>$reqPermission){
    if($reqPermission > $permission){
        continue;
    }
    $resultColumns[] = $column;
}

foreach($rawGeneralList as $rawGeneral){
    //General 생성?
    
    $item = [];
    foreach($resultColumns as $column){
        $value = $rawGeneral[$column];
        if(key_exists($column, $specialViewFilter)){
            $value = $specialViewFilter[$column]($rawGeneral);
        }
        $item[] = $value;
    }

    $generalList[] = $item;
}

$result = [
    'result'=>true,
    'column'=>$resultColumns,
    'list'=>$generalList,
];

Json::die($result);
