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

$me = $db->queryFirstRow('SELECT no,nation,level,con,turntime,belong FROM general WHERE owner=%i', $userID);

[$nationLevel, $secretLimit] = $db->queryFirstList('SELECT level, secretlimit FROM nation WHERE nation = %i', $me['nation']);

$con = checkLimit($me['con']);
if($con >= 2) { printLimitMsg($me['turntime']); exit(); }

if($me['level'] == 0 || ($me['level'] == 1 && $me['belong'] < $secretLimit)) {
    Json::die([
        'result'=>false,
        'reason'=>'수뇌부가 아니거나 사관년도가 부족합니다'
    ]);
}

$date = TimeUtil::now();

// 명령 목록
[$year, $month, $turnterm] = $gameStor->getValuesAsArray(['year', 'month', 'turnterm']);
$lv = getNationChiefLevel($nationLevel);
$turn = [];

$generals = [];
foreach($db->query('SELECT no,name,turntime,npc,city,nation,level FROM general WHERE nation = %i AND level >= 5') as $rawGeneral){
    $generals[$rawGeneral['level']] = new General($rawGeneral, null, $year, $month, false);
}

$nationTurnList = [];

foreach(
    $db->queryAllLists(
        'SELECT level, turn_idx, action, arg FROM nation_turn WHERE nation_id = %i ORDER BY level DESC, turn_idx ASC',
        $me['nation']
    ) as [$level, $turn_idx, $action, $arg]
){
    if(!key_exists($level, $nationTurnList)){
        $nationTurnList[$level] = [];
    }
    $nationTurnList[$level][$turn_idx] = [$action, Json::decode($arg)];
}

$nationTurnBrief = [];
foreach($nationTurnList as $level=>$turnList){
    if(!key_exists($level, $generals)){
        $general = Util::array_first($generals);
    }
    else{
        $general = $generals[$level];
    }
    $nationTurnBrief[$level] = [
        'name'=>$general->getName(),
        'turnTime'=>substr($general->getVar('turntime'), 11, 5),
        'levelText'=>getLevel($general->getVar('level'), $nationLevel),
        'npcType'=>$general->getVar('npc'),
        'turn'=>getNationTurnBrief($general, $turnList)
    ];
}

$date = substr(TimeUtil::now(), 14);

Json::die([
    'result'=>true,
    'reason'=>'success',
    'date'=>$date,
    'nationTurnBrief'=>$nationTurnBrief,
    'isChief'=>($me['level'] > 4)
]);