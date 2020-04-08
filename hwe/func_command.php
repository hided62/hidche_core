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
        return;
    }
    if($turnCnt >= GameConst::$maxTurn){
        return;
    }

    $db = DB::db();

    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx + %i', $turnCnt)
    ], 'general_id=%i ORDER BY turn_idx DESC', $generalID);
    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', GameConst::$maxTurn),
        'action'=>'휴식',
        'arg'=>'{}',
        'brief'=>'휴식'
    ], 'general_id=%i AND turn_idx >= %i', $generalID, GameConst::$maxTurn);
}

function pullGeneralCommand(int $generalID, int $turnCnt=1){
    if($turnCnt == 0){
        return;
    }
    if($turnCnt < 0){
        pushGeneralCommand($generalID, -$turnCnt);
        return;
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

function repeatGeneralCommand(int $generalId, int $turnCnt){
    if($turnCnt <= 0){
        return;
    }
    if($turnCnt >= GameConst::$maxTurn){
        return;
    }

    $db = DB::db();

    $reqTurn = $turnCnt;
    if($turnCnt * 2 > GameConst::$maxTurn){
        $reqTurn = GameConst::$maxTurn - $turnCnt;
    }

    $turnList = $db->query('SELECT turn_idx, `action`, arg, brief FROM general_turn WHERE general_id=%i AND turn_idx < %i', $generalId, $reqTurn);
    foreach($turnList as $turnItem){
        $turnIdx = $turnItem['turn_idx'];
        $turnTarget = iterator_to_array(Util::range($turnIdx+$turnCnt, GameConst::$maxTurn, $turnCnt));
        
        $db->update('general_turn', [
            'action'=>$turnItem['action'],
            'arg'=>$turnItem['arg'],
            'brief'=>$turnItem['brief']
        ], 'general_id=%i AND turn_idx IN %li', $generalId, $turnTarget);
    }
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
        return;
    }
    if($turnCnt >= GameConst::$maxChiefTurn){
        return;
    }

    $db = DB::db();

    $db->update('nation_turn', [
        'turn_idx'=>$db->sqleval('turn_idx + %i', $turnCnt)
    ], 'nation_id=%i AND level=%i ORDER BY turn_idx DESC', $nationID, $level);
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
        return;
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
            'amount', 'colorType',
        ],
        'boolean'=>[
            'isGold', 'buyRice',
        ],
        'between'=>[
            ['month', [1, 12]]
        ],
        'min'=>[
            ['year', 0],
            ['destGeneralID', 1],
            ['destCityID', 1],
            ['destNationID', 1],
            ['amount', 1],
            ['crewType', 0]
        ],
        'integerArray'=>[
            'destNationIDList', 'destGeneralIDList', 'amountList'
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

function setGeneralCommand(int $generalID, array $rawTurnList, string $command, ?array $arg = null):array{
    
    $turnList = [];
    foreach($rawTurnList as $turnIdx){
        if(!is_int($turnIdx) || $turnIdx < -3 || $turnIdx >= GameConst::$maxTurn){
            return [
                'result'=>false,
                'reason'=>'올바른 턴이 아닙니다. : '.$turnIdx,
                'test'=>'turnIdx',
                'target'=>$turnIdx,
            ];
        }
        if($turnIdx >= 0){
            $turnList[$turnIdx] = true;
        }
        else if($turnIdx == -1){
            //홀수
            for ($subIdx = 1; $subIdx < GameConst::$maxTurn; $subIdx+=2) {
               $turnList[$subIdx] = true;
            }
        }
        else if($turnIdx == -2){
            //짝수
            for ($subIdx = 0; $subIdx < GameConst::$maxTurn; $subIdx+=2) {
                $turnList[$subIdx] = true;
            }
        }
        else if($turnIdx == -3){
            //모두
            for ($subIdx = 0; $subIdx < GameConst::$maxTurn; $subIdx++) {
                $turnList[$subIdx] = true;
            }
        }
    }
    $turnList = array_keys($turnList);

    $argBasicTestResult = checkCommandArg($arg);
    if($argBasicTestResult !== null){
        return [
            'result'=>false,
            'reason'=>'턴이 입력되지 않았습니다.',
            'test'=>'checkCommandArg',
            'target'=>'arg'
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
            'test'=>'build',
            'target'=>'arg'
        ];
    }
    catch (\Exception $e){
        return [
            'result'=>false,
            'reason'=>$e->getCode().$e->getMessage(),
            'test'=>'build',
            'target'=>'arg'
        ];
    }

    if(!$commandObj->isArgValid()){
        return [
            'result'=>false,
            'reason'=>'올바르지 않은 argument',
            'test'=>'isArgValid',
            'target'=>'arg'
        ];
    }

    /*if(!$commandObj->isReservable()){
        return [
            'result'=>false,
            'reason'=>'예약 불가능한 커맨드 :'.$commandObj->testReservable(),
            'test'=>'isReservable',
            'target'=>'command'
        ];
    }*/

    $brief = $commandObj->getBrief();

    _setGeneralCommand($generalID, $turnList, $command, $arg, $brief);
    return [
        'result'=>true,
        'reason'=>'success'
    ];
}

function setNationCommand(int $generalID, array $turnList, string $command, ?array $arg = null):array{
    $turnList = array_unique($turnList);
    foreach($turnList as $turnIdx){
        if(!is_int($turnIdx) || $turnIdx < 0 || $turnIdx >= GameConst::$maxChiefTurn){
            return [
                'result'=>false,
                'reason'=>'올바른 턴이 아닙니다. : '.$turnIdx,
                'test'=>'turnIdx',
                'target'=>$turnIdx,
            ];
        }
    }

    $argBasicTestResult = checkCommandArg($arg);
    if($argBasicTestResult !== null){
        return [
            'result'=>false,
            'reason'=>'턴이 입력되지 않았습니다.',
            'test'=>'checkCommandArg',
            'target'=>'arg'
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
            'test'=>'build',
            'target'=>'arg'
        ];
    }
    catch (\Exception $e){
        return [
            'result'=>false,
            'reason'=>$e->getCode().$e->getMessage(),
            'test'=>'build',
            'target'=>'arg'
        ];
    }

    if(!$commandObj->isArgValid()){
        return [
            'result'=>false,
            'reason'=>'올바르지 않은 argument',
            'test'=>'isArgValid',
            'target'=>'arg'
        ];
    }

    /*if(!$commandObj->isReservable()){
        return [
            'result'=>false,
            'reason'=>'예약 불가능한 커맨드 :'.$commandObj->testReservable(),
            'test'=>'isReservable',
            'target'=>'command'
        ];
    }*/
    //TODO: Reservable은 '정말로 입력 불가'이고, '입력은 가능하지만 실행은 안될 것 같은' 군을 하나더 추가해야함
    //      Runnable은 Arg를 모두 받아서 처리해야 하는 것이고, Arg를 받지 않아도 안될 것 같지만 입력 자체는 가능할 것 같은 커맨드.

    $brief = $commandObj->getBrief();

    _setNationCommand($general->getNationID(), $general->getVar('level'), $turnList, $command, $arg, $brief);
    return [
        'result'=>true,
        'arg_test'=>true,
        'reason'=>'success'
    ];
}