<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("사령부", 1);

$me = $db->queryFirstRow('SELECT no,nation,officer_level,con,turntime,belong,penalty,permission FROM general WHERE owner=%i', $userID);

$nationLevel = $db->queryFirstField('SELECT level FROM nation WHERE nation = %i', $me['nation']);
$nationID = $me['nation'];
$con = checkLimit($me['con']);
if($con >= 2) { 
    Json::die([
        'result'=>false,
        'reason'=>"접속 제한중입니다. 1턴 이내에 너무 많은 갱신을 하셨습니다. (다음 접속 가능 시각 : {$me['turntime']})"
    ]);
}

$permission = checkSecretPermission($me);
if($permission < 0){
    Json::die([
        'result'=>false,
        'reason'=>'국가에 소속되어있지 않습니다.'
    ]);
    echo '국가에 소속되어있지 않습니다.';
    die();
}
else if ($permission < 1) {
    Json::die([
        'result'=>false,
        'reason'=>'수뇌부가 아니거나 사관년도가 부족합니다.'
    ]);
    die();
}

$date = TimeUtil::now();

// 명령 목록
[$year, $month, $turnterm] = $gameStor->getValuesAsArray(['year', 'month', 'turnterm']);
$lv = getNationChiefLevel($nationLevel);
$turn = [];

$generals = [];
foreach($db->query('SELECT no,name,turntime,npc,city,nation,officer_level FROM general WHERE nation = %i AND officer_level >= 5',$nationID) as $rawGeneral){
    $generals[$rawGeneral['officer_level']] = new General($rawGeneral, null, null, null, $year, $month, false);
}

$nationTurnList = [];

foreach(
    $db->queryAllLists(
        'SELECT officer_level, turn_idx, action, arg, brief FROM nation_turn WHERE nation_id = %i ORDER BY officer_level DESC, turn_idx ASC',
        $me['nation']
    ) as [$officer_level, $turn_idx, $action, $arg, $brief]
){
    if(!key_exists($officer_level, $nationTurnList)){
        $nationTurnList[$officer_level] = [];
    }
    $nationTurnList[$officer_level][$turn_idx] = $brief;
}

$nationTurnBrief = [];
foreach($nationTurnList as $officer_level=>$turnBrief){
    if(!key_exists($officer_level, $generals)){
        $nationTurnBrief[$officer_level] = [
            'name'=>null,
            'turnTime'=>null,
            'officerLevelText'=>getOfficerLevelText($officer_level, $nationLevel),
            'npcType'=>null,
            'turn'=>$turnBrief
        ];
        continue;
    }
    $general = $generals[$officer_level];
    $nationTurnBrief[$officer_level] = [
        'name'=>$general->getName(),
        'turnTime'=>$general->getTurnTime($general::TURNTIME_FULL),
        'officerLevelText'=>getOfficerLevelText($general->getVar('officer_level'), $nationLevel),
        'npcType'=>$general->getNPCType(),
        'turn'=>$turnBrief
    ];
}

$date = substr(TimeUtil::now(), 14);

Json::die([
    'result'=>true,
    'reason'=>'success',
    'date'=>$date,
    'nationTurnBrief'=>$nationTurnBrief,
    'isChief'=>($me['officer_level'] > 4),
    'turnTerm'=>$turnterm
]);