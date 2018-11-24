<?php
namespace sammo;

function getGeneralTurnBrief(General $generalObj, array $turnList) {
    $result = [];

    foreach($turnList as $turnIdx => [$action, $arg]){
        $commandObj = buildGeneralCommandClass($action, $generalObj, [], $arg);
        $turnText = $commandObj->getBrief();
        $result[$turnIdx] = $turnText;
    }
    return $result;
}

function getNationTurnBrief(General $generalObj, array $turnList) {
    $result = [];

    $tmpTurn = new LastTurn();
    foreach($turnList as $turnIdx => [$action, $arg]){
        $commandObj = buildNationCommandClass($action, $generalObj, [], $tmpTurn, $arg);
        $turnText = $commandObj->getBrief();
        $result[$turnIdx] = $turnText;
    }
    return $result;
}

function pushGeneralCommand(int $generalID, int $turnCnt=1){
    if($turnCnt == 0){
        return;
    }
    if($turnCnt < 0){
        pullGeneralCommand($generalID, -$turnCnt);   
    }
    if($turnCnt >= GameConst::$maxTurn){
        return;
    }

    $db = DB::db();

    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx + %i', $turnCnt)
    ], 'general_id=%i', $generalID);
    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', GameConst::$maxTurn),
        'action'=>'휴식',
        'arg'=>'{}'
    ], 'general_id=%i AND turn_idx >= %i', $generalID, GameConst::$maxTurn);
}

function pullGeneralCommand(int $generalID, int $turnCnt=1){
    if($turnCnt == 0){
        return;
    }
    if($turnCnt < 0){
        pushGeneralCommand($generalID, -$turnCnt);
    }
    if($turnCnt >= GameConst::$maxTurn){
        return;
    }
    
    $db = DB::db();

    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx + %i', GameConst::$maxTurn),
        'action'=>'휴식',
        'arg'=>'{}'
    ], 'general_id=%i AND turn_idx < %i', $generalID, $turnCnt);
    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', $turnCnt)
    ], 'general_id=%i', $generalID);
}

function pushNationCommand(int $nationID, int $level, int $turnCnt=1){
    if($nationID == 0){
        return;
    }
    if($level < 5){
        return;
    }
    if($turnCnt == 0){
        return;
    }
    if($turnCnt < 0){
        pullNationCommand($nationID, $level, -$turnCnt);   
    }
    if($turnCnt >= GameConst::$maxChiefTurn){
        return;
    }

    $db = DB::db();

    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx + %i', $turnCnt)
    ], 'nation_id=%i AND level=%i', $nationID, $level);
    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', GameConst::$maxChiefTurn),
        'action'=>'휴식',
        'arg'=>'{}'
    ], 'nation_id=%i AND level=%i AND turn_idx >= %i', $nationID, $level, GameConst::$maxChiefTurn);
}

function pullNationCommand(int $nationID, int $level, int $turnCnt=1){
    if($nationID == 0){
        return;
    }
    if($level < 5){
        return;
    }
    if($turnCnt == 0){
        return;
    }
    if($turnCnt < 0){
        pushNationCommand($nationID, $level, -$turnCnt);
    }
    if($turnCnt >= GameConst::$maxChiefTurn){
        return;
    }
    
    $db = DB::db();

    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx + %i', GameConst::$maxChiefTurn),
        'action'=>'휴식',
        'arg'=>'{}'
    ], 'nation_id=%i AND level=%i AND turn_idx < %i', $nationID, $level, $turnCnt);
    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', $turnCnt)
    ], 'nation_id=%i AND level=%i', $nationID, $level);
}

function setGeneralCommand(int $generalID, array $turnList, string $command, ?array $arg = null) {
    if(!$turnList){
        return;
    }

    $db = DB::db();

    $db->update('general_turn', [
        'action'=>$command,
        'arg'=>Json::encode($arg, JSON::EMPTY_ARRAY_IS_DICT)
    ], 'general_id = %i AND turn_idx IN %li', $generalID, $turnList);
}

function setNationCommand(int $nationID, int $level, array $turnList, string $command, ?array $arg = null) {
    if(!$turnList){
        return;
    }

    $db = DB::db();

    $db->update('nation_turn', [
        'action'=>$command,
        'arg'=>Json::encode($arg, JSON::EMPTY_ARRAY_IS_DICT)
    ], 'nation_id = %i AND level = %i AND turn_idx IN %li', $generalID, $level, $turnList);
}
