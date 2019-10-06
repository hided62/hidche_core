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
        'arg'=>'{}',
        'brief'=>'휴식'
    ], 'general_id=%i AND turn_idx >= %i ORDER BY turn_idx ASC', $generalID, GameConst::$maxTurn);
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
        'arg'=>'{}',
        'brief'=>'휴식'
    ], 'general_id=%i AND turn_idx < %i', $generalID, $turnCnt);
    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', $turnCnt)
    ], 'general_id=%i ORDER BY turn_idx ASC', $generalID);
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

    $db->update('nation_turn', [
        'turn_idx'=>$db->sqleval('turn_idx + %i', $turnCnt)
    ], 'nation_id=%i AND level=%i', $nationID, $level);
    $db->update('nation_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', GameConst::$maxChiefTurn),
        'action'=>'휴식',
        'arg'=>'{}',
        'brief'=>'휴식'
    ], 'nation_id=%i AND level=%i AND turn_idx >= %i ORDER BY turn_idx ASC', $nationID, $level, GameConst::$maxChiefTurn);
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

    $db->update('nation_turn', [
        'turn_idx'=>$db->sqleval('turn_idx + %i', GameConst::$maxChiefTurn),
        'action'=>'휴식',
        'arg'=>'{}',
        'brief'=>'휴식',
    ], 'nation_id=%i AND level=%i AND turn_idx < %i', $nationID, $level, $turnCnt);
    $db->update('nation_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', $turnCnt)
    ], 'nation_id=%i AND level=%i ORDER BY turn_idx ASC', $nationID, $level);
}

function _setGeneralCommand(int $generalID, array $turnList, string $command, ?array $arg, string $brief) {
    if(!$turnList){
        return;
    }

    $db = DB::db();

    $db->update('general_turn', [
        'action'=>$command,
        'arg'=>Json::encode($arg, JSON::EMPTY_ARRAY_IS_DICT),
        'brief'=>$brief
    ], 'general_id = %i AND turn_idx IN %li', $generalID, $turnList);
}

function _setNationCommand(int $nationID, int $level, array $turnList, string $command, ?array $arg, string $brief) {
    if(!$turnList){
        return;
    }

    $db = DB::db();

    $db->update('nation_turn', [
        'action'=>$command,
        'arg'=>Json::encode($arg, JSON::EMPTY_ARRAY_IS_DICT),
        'brief'=>$brief
    ], 'nation_id = %i AND level = %i AND turn_idx IN %li', $nationID, $level, $turnList);
}

function checkCommandArg(?array $arg):?string{
    if($arg === null){
        return null;
    }
    $defaultCheck = [
        /*'string'=>[
            'nationName', 'optionText', 'itemType', 'nationType'
        ],*/
        'integer'=>[
            'crewType', 'destGeneralID', 'destCityID', 'destNationID',
            'amount', 'colorType', 'itemCode'
        ],
        'boolean'=>[
            'isGold', 'buyRice',
        ],
        'between'=>[
            ['month', [1, 12]]
        ],
        'min'=>[
            ['year', 0],
            ['itemCode', 0],
            ['destGeneralID', 1],
            ['destCityID', 1],
            ['destNationID', 1],
            ['amount', 1],
            ['crewType', 0]
        ],
        'integerArray'=>[
            'destNationIDList', 'destGeneralIDList'
        ],
        'stringWidthBetween'=>[
            ['nationName', 1, 18]
        ]
    ];
    $v = new Validator($arg);
    $v->rules($defaultCheck);
    if (!$v->validate()){
        return $v->errorStr();
    }
    return null;
}

function setGeneralCommand(int $generalID, array $turnList, string $command, ?array $arg = null):array{
    $turnList = array_unique($turnList);
    foreach($turnList as $turnIdx){
        if(!is_int($turnIdx) || $turnIdx < 0 || $turnIdx >= GameConst::$maxTurn){
            return [
                'result'=>false,
                'reason'=>'올바른 턴이 아닙니다. : '.$turnIdx
            ];
        }
    }

    $argBasicTestResult = checkCommandArg($arg);
    if($argBasicTestResult !== null){
        return [
            'result'=>false,
            'reason'=>'턴이 입력되지 않았습니다.'
        ];
    }

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $env = $gameStor->getAll();
    $general = General::createGeneralObjFromDB($generalID);

    try{
        $commandObj = buildGeneralCommandClass($command, $general, $env, $arg);
    }
    catch (\InvalidArgumentException $e){
        return [
            'result'=>false,
            'reason'=>$e->getMessage(),
        ];
    }
    catch (\Exception $e){
        return [
            'result'=>false,
            'reason'=>$e->getCode().$e->getMessage()
        ];
    }

    if(!$commandObj->isArgValid()){
        return [
            'result'=>false,
            'arg_test'=>false,
            'reason'=>'올바르지 않은 argument'
        ];
    }

    if(!$commandObj->isReservable()){
        return [
            'result'=>false,
            'arg_test'=>true,
            'reason'=>'예약 불가능한 커맨드 :'.$commandObj->testReservable()
        ];
    }

    $brief = $commandObj->getBrief();

    _setGeneralCommand($generalID, $turnList, $command, $arg, $brief);
    return [
        'result'=>true,
        'arg_test'=>true,
        'reason'=>'success'
    ];
}

function setNationCommand(int $generalID, array $turnList, string $command, ?array $arg = null):array{
    $turnList = array_unique($turnList);
    foreach($turnList as $turnIdx){
        if(!is_int($turnIdx) || $turnIdx < 0 || $turnIdx >= GameConst::$maxChiefTurn){
            return [
                'result'=>false,
                'reason'=>'올바른 턴이 아닙니다. : '.$turnIdx
            ];
        }
    }

    $argBasicTestResult = checkCommandArg($arg);
    if($argBasicTestResult !== null){
        return [
            'result'=>false,
            'reason'=>'턴이 입력되지 않았습니다.'
        ];
    }

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $env = $gameStor->getAll();
    $general = General::createGeneralObjFromDB($generalID);

    if($general->getVar('level') < 5){
        return [
            'result'=>false,
            'reason'=>'수뇌가 아닙니다'
        ];
    }

    try{
        $commandObj = buildNationCommandClass($command, $general, $env, $general->getLastTurn(), $arg);
    }
    catch (\InvalidArgumentException $e){
        return [
            'result'=>false,
            'reason'=>$e->getMessage(),
        ];
    }
    catch (\Exception $e){
        return [
            'result'=>false,
            'reason'=>$e->getCode().$e->getMessage()
        ];
    }

    if(!$commandObj->isArgValid()){
        return [
            'result'=>false,
            'arg_test'=>false,
            'reason'=>'올바르지 않은 argument'
        ];
    }

    if(!$commandObj->isReservable()){
        return [
            'result'=>false,
            'arg_test'=>true,
            'reason'=>'예약 불가능한 커맨드 :'.$commandObj->testReservable()
        ];
    }

    _setNationCommand($general->getNationID(), $general->getVar('level'), $turnList, $command, $arg);
    return [
        'result'=>true,
        'arg_test'=>true,
        'reason'=>'success'
    ];
}