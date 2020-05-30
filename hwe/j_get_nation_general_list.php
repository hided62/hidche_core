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

$me = $db->queryFirstRow('SELECT con, turntime, belong, nation, officer_level, permission, penalty FROM general WHERE owner=%i', $userID);
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
    'no'=>0,
    'name'=>0,
    'owner_name'=>9,
    'nation'=>0,
    'city'=>1,
    'officer_level'=>0,
    'officer_city'=>2,
    'npc'=>0,
    'defence_train'=>2,
    'troop'=>2,
    'injury'=>0,
    'leadership'=>0,
    'strength'=>0,
    'intel'=>0,
    'experience'=>1,
    'explevel'=>0,
    'dedication'=>1,
    'dedlevel'=>0,
    'gold'=>0,
    'rice'=>0,
    'crewtype'=>2,
    'crew'=>2,
    'train'=>2,
    'atmos'=>2,
    'killturn'=>1,
    'turntime'=>2,
    'picture'=>0,
    'imgsvr'=>0,
    'age'=>0,
    'special'=>0,
    'special2'=>0,
    'personal'=>0,
    'belong'=>0,
    'horse'=>2,
    'weapon'=>2,
    'book'=>2,
    'item'=>2,
    'connect'=>0
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

[$queryColumns, $rankColumns] = General::mergeQueryColumn(array_keys($viewColumns), 1);

$rawGeneralList = Util::convertArrayToDict($db->query('SELECT %l from general WHERE nation = %i', Util::formatListOfBackticks($queryColumns), $nationID), 'no');

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
