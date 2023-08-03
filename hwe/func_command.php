<?php
namespace sammo;

use sammo\Command\GeneralCommand;
use sammo\Command\NationCommand;

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

function pushNationCommand(int $nationID, int $officerLevel, int $turnCnt=1){
    if($nationID == 0){
        return;
    }
    if($officerLevel < 5){
        return;
    }
    if($turnCnt == 0){
        return;
    }
    if($turnCnt < 0){
        pullNationCommand($nationID, $officerLevel, -$turnCnt);
        return;
    }
    if($turnCnt >= GameConst::$maxChiefTurn){
        return;
    }

    $db = DB::db();

    $db->update('nation_turn', [
        'turn_idx'=>$db->sqleval('turn_idx + %i', $turnCnt)
    ], 'nation_id=%i AND officer_level=%i ORDER BY turn_idx DESC', $nationID, $officerLevel);
    $db->update('nation_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', GameConst::$maxChiefTurn),
        'action'=>'휴식',
        'arg'=>'{}',
        'brief'=>'휴식'
    ], 'nation_id=%i AND officer_level=%i AND turn_idx >= %i ORDER BY turn_idx ASC', $nationID, $officerLevel, GameConst::$maxChiefTurn);
}

function pullNationCommand(int $nationID, int $officerLevel, int $turnCnt=1){
    if($nationID == 0){
        return;
    }
    if($officerLevel < 5){
        return;
    }
    if($turnCnt == 0){
        return;
    }
    if($turnCnt < 0){
        pushNationCommand($nationID, $officerLevel, -$turnCnt);
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
    ], 'nation_id=%i AND officer_level=%i AND turn_idx < %i', $nationID, $officerLevel, $turnCnt);
    $db->update('nation_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', $turnCnt)
    ], 'nation_id=%i AND officer_level=%i ORDER BY turn_idx ASC', $nationID, $officerLevel);
}

function repeatNationCommand(int $nationID, int $officerLevel, int $turnCnt){
    if($turnCnt <= 0){
        return;
    }
    if($turnCnt >= GameConst::$maxChiefTurn){
        return;
    }

    $db = DB::db();

    $reqTurn = $turnCnt;
    if($turnCnt * 2 > GameConst::$maxChiefTurn){
        $reqTurn = GameConst::$maxChiefTurn - $turnCnt;
    }

    $turnList = $db->query('SELECT turn_idx, `action`, arg, brief FROM nation_turn WHERE nation_id=%i AND officer_level=%i AND turn_idx < %i', $nationID, $officerLevel, $reqTurn);
    if(!$turnList){
        return;
    }
    foreach($turnList as $turnItem){
        $turnIdx = $turnItem['turn_idx'];
        $turnTarget = iterator_to_array(Util::range($turnIdx+$turnCnt, GameConst::$maxChiefTurn, $turnCnt));

        $db->update('nation_turn', [
            'action'=>$turnItem['action'],
            'arg'=>$turnItem['arg'],
            'brief'=>$turnItem['brief']
        ], 'nation_id=%i AND officer_level=%i AND turn_idx IN %li', $nationID, $officerLevel, $turnTarget);
    }
}


function _setGeneralCommand(GeneralCommand $command, array $turnList):void {
    if(!$turnList){
        return;
    }

    $db = DB::db();

    $generalID = $command->getGeneral()->getID();
    $commandName = $command->getRawClassName();
    $arg = $command->getArg();
    $brief = $command->getBrief();

    $db->update('general_turn', [
        'action'=>$commandName,
        'arg'=>Json::encode($arg, JSON::EMPTY_ARRAY_IS_DICT),
        'brief'=>$brief
    ], 'general_id = %i AND turn_idx IN %li', $generalID, $turnList);
}

function _setNationCommand(NationCommand $command, array $turnList):void  {
    if(!$turnList){
        return;
    }

    $db = DB::db();

    $nationID = $command->getNationID();
    $officerLevel = $command->getOfficerLevel();
    $commandName = $command->getRawClassName();
    $arg = $command->getArg();
    $brief = $command->getBrief();

    $db->update('nation_turn', [
        'action'=>$commandName,
        'arg'=>Json::encode($arg, JSON::EMPTY_ARRAY_IS_DICT),
        'brief'=>$brief
    ], 'nation_id = %i AND officer_level = %i AND turn_idx IN %li', $nationID, $officerLevel, $turnList);
}

function sanitizeArg(?array $args):?array{
    if($args === null){
        return $args;
    }

    $result = [];
    foreach($args as $key=>$value){
        if(is_array($value)){
            $result[$key] = sanitizeArg($value);
        }
        else if(is_string($value)){
            $result[$key] = StringUtil::neutralize(StringUtil::removeSpecialCharacter($value));
        }
        else{
            $result[$key] = $value;
        }
    }
    return $result;
}

function checkCommandArg(?array $arg):?string{
    if($arg === null){
        return null;
    }
    $defaultCheck = [
        /*'string'=>[
            'nationName', 'optionText', 'itemType', 'nationType', 'commandType',
        ],*/
        'int'=>[
            'crewType', 'destGeneralID', 'destCityID', 'destNationID',
            'amount', 'colorType',
            'srcArmType', 'destArmType', //숙련전환 전용
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
            //홀수, 0부터 맞다.
            foreach(Util::range(0, GameConst::$maxTurn, 2) as $subIdx){
                $turnList[$subIdx] = true;
            }
        }
        else if($turnIdx == -2){
            //짝수, 1부터 맞다.
            foreach(Util::range(1, GameConst::$maxTurn, 2) as $subIdx){
                $turnList[$subIdx] = true;
            }
        }
        else if($turnIdx == -3){
            //모두
            foreach(Util::range(GameConst::$maxTurn) as $subIdx){
                $turnList[$subIdx] = true;
            }
        }
    }
    $turnList = array_keys($turnList);

    $arg = sanitizeArg($arg);
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
    $general = General::createObjFromDB($generalID);

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

    if(!$commandObj->hasPermissionToReserve()){
        return [
            'result'=>false,
            'reason'=>'예약 불가능한 커맨드 :'.$commandObj->testPermissionToReserve(),
            'test'=>'hasPermissionToReserve',
            'target'=>'command'
        ];
    }

    _setGeneralCommand($commandObj, $turnList);
    return [
        'result'=>true,
        'brief'=>$commandObj->getBrief(),
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

    $arg = sanitizeArg($arg);
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
    $general = General::createObjFromDB($generalID);

    if($general->getVar('officer_level') < 5){
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

    if(!$commandObj->hasPermissionToReserve()){
        return [
            'result'=>false,
            'reason'=>'예약 불가능한 커맨드 :'.$commandObj->testPermissionToReserve(),
            'test'=>'hasPermissionToReserve',
            'target'=>'command'
        ];
    }

    _setNationCommand($commandObj, $turnList);
    return [
        'result'=>true,
        'brief'=>$commandObj->getBrief(),
        'arg_test'=>true,
        'reason'=>'success'
    ];
}