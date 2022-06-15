<?php

namespace sammo\Command\Nation;

use \sammo\{
    DB,
    Util,
    JosaUtil,
    General,
    DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command,
    KVStorage,
    Message,
    MessageTarget
};

use function \sammo\buildNationCommandClass;
use function \sammo\getAllNationStaticInfo;
use function \sammo\getNationStaticInfo;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_피장파장 extends Command\NationCommand
{
    static protected $actionName = '피장파장';
    static public $reqArg = true;
    static public $delayCnt = 60;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        //NOTE: 멸망 직전에 턴을 넣을 수 있으므로, 존재하지 않는 국가여도 argTest에서 바로 탈락시키지 않음
        if (!key_exists('destNationID', $this->arg)) {
            return false;
        }
        if (!key_exists('commandType', $this->arg)) {
            return false;
        }
        $destNationID = $this->arg['destNationID'];
        $commandType = $this->arg['commandType'];

        if (!is_int($destNationID)) {
            return false;
        }
        if ($destNationID < 1) {
            return false;
        }

        if (!is_string($commandType)) {
            return false;
        }
        if (!in_array($commandType, GameConst::$availableChiefCommand['전략'])) {
            return false;
        }


        $this->arg = [
            'destNationID' => $destNationID,
            'commandType' => $commandType
        ];
        return true;
    }

    protected function init()
    {
        $general = $this->generalObj;

        $env = $this->env;

        $this->setCity();
        $this->setNation();

        $this->minConditionConstraints = [
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
        ];
    }

    protected function initWithArg()
    {
        $this->setDestNation($this->arg['destNationID'], null);

        if ($this->getNationID() == 0) {
            $this->fullConditionConstraints = [
                ConstraintHelper::OccupiedCity()
            ];
            return;
        }

        $cmd = buildNationCommandClass($this->arg['commandType'], $this->generalObj, $this->env, new LastTurn());

        $currYearMonth = Util::joinYearMonth($this->env['year'], $this->env['month']);
        $nextAvailableTurn = $cmd->getNextAvailableTurn();
        if ($currYearMonth < $nextAvailableTurn) {
            $this->fullConditionConstraints = [
                ConstraintHelper::AlwaysFail('해당 전략을 아직 사용할 수 없습니다')
            ];
            return;
        }

        $this->fullConditionConstraints = [
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::AllowDiplomacyBetweenStatus(
                [0, 1],
                '선포, 전쟁중인 상대국에게만 가능합니다.'
            ),
        ];
    }

    public function getCommandDetailTitle(): string
    {
        $name = $this->getName();
        $reqTurn = $this->getPreReqTurn() + 1;

        return "{$name}/{$reqTurn}턴(대상 재사용 대기 {$this->getTargetPostReqTurn()})";
    }

    public function getCost(): array
    {
        return [0, 0];
    }

    public function getPreReqTurn(): int
    {
        return 1;
    }

    public function getPostReqTurn(): int
    {
        return 0;
    }

    public function getTargetPostReqTurn(): int
    {
        $genCount = Util::valueFit($this->nation['gennum'], GameConst::$initialNationGenLimit);
        $nextTerm = Util::round(sqrt($genCount * 2) * 10);

        $nextTerm = $this->generalObj->onCalcStrategic($this->getName(), 'delay', $nextTerm);
        $nextTerm = Util::valueFit($nextTerm, Util::round(static::$delayCnt * 1.2));
        return $nextTerm;
    }

    public function getBrief(): string
    {
        $commandName = $this->getName();
        $cmd = buildNationCommandClass($this->arg['commandType'], $this->generalObj, $this->env, new LastTurn());
        $targetCommandName = $cmd->getName();
        $destNationName = getNationStaticInfo($this->arg['destNationID'])['name'];
        return "【{$destNationName}】에 【{$targetCommandName}】 {$commandName}";
    }


    public function run(\Sammo\RandUtil $rng): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $generalID = $general->getID();
        $generalName = $general->getName();
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $year = $this->env['year'];
        $month = $this->env['month'];

        $nation = $this->nation;
        $nationID = $nation['nation'];
        $nationName = $nation['name'];

        $destNation = $this->destNation;
        $destNationID = $destNation['nation'];
        $destNationName = $destNation['name'];

        $josaYi = JosaUtil::pick($generalName, '이');
        $josaYiNation = JosaUtil::pick($nationName, '이');

        $commandName = $this->getName();
        $josaUl = JosaUtil::pick($commandName, '을');

        $cmd = buildNationCommandClass($this->arg['commandType'], $this->generalObj, $this->env, new LastTurn());


        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("<G><b>{$cmd->getName()}</b></> 전략의 {$commandName} 발동! <1>$date</>");

        $general->addExperience(5 * ($this->getPreReqTurn() + 1));
        $general->addDedication(5 * ($this->getPreReqTurn() + 1));

        $broadcastMessage = "<Y>{$generalName}</>{$josaYi} <G><b>{$destNationName}</b></>에 <G><b>{$cmd->getName()}</b></> 전략의 <M>{$commandName}</>{$josaUl} 발동하였습니다.";

        $nationGeneralList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no != %i', $nationID, $generalID);
        foreach ($nationGeneralList as $nationGeneralID) {
            $nationGeneralLogger = new ActionLogger($nationGeneralID, $nationID, $year, $month);
            $nationGeneralLogger->pushGeneralActionLog($broadcastMessage, ActionLogger::PLAIN);
            $nationGeneralLogger->flush();
        }

        $josaYiCommand = JosaUtil::pick($commandName, '이');

        $broadcastMessage = "아국에 <G><b>{$cmd->getName()}</b></> 전략의 <M>{$commandName}</>{$josaYiCommand} 발동되었습니다.";

        $destNationGeneralList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i', $destNationID);
        foreach ($destNationGeneralList as $destNationGeneralID) {
            $destNationGeneralLogger = new ActionLogger($destNationGeneralID, $destNationID, $year, $month);
            $destNationGeneralLogger->pushGeneralActionLog($broadcastMessage, ActionLogger::PLAIN);
            $destNationGeneralLogger->flush();
        }

        $destNationLogger = new ActionLogger(0, $destNationID, $year, $month);
        $destNationLogger->pushNationalHistoryLog("<D><b>{$nationName}</b></>의 <Y>{$generalName}</>{$josaYi} 아국에 <G><b>{$cmd->getName()}</b></> <M>{$commandName}</>{$josaUl} 발동");
        $destNationLogger->flush();

        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <D><b>{$destNationName}</b></>에 <G><b>{$cmd->getName()}</b></> <M>{$commandName}</>{$josaUl} 발동");

        $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
        $destNationStor = KVStorage::getStorage($db, $destNationID, 'nation_env');

        $yearMonth = Util::joinYearMonth($env['year'], $env['month']);
        $nationStor->setValue($cmd->getNextExecuteKey(), $yearMonth + $this->getTargetPostReqTurn());

        $destDelay = max($destNationStor->getValue($cmd->getNextExecuteKey()) ?? 0, $yearMonth);
        $destNationStor->setValue($cmd->getNextExecuteKey(), $destDelay + static::$delayCnt);

        $general->applyDB($db);

        return true;
    }

    public function exportJSVars(): array
    {
        $generalObj = $this->generalObj;
        $nationID = $generalObj->getNationID();

        $testTurn = new LastTurn($this->getName(), null, null);

        $availableCommandTypeList = [];
        $currYearMonth = Util::joinYearMonth($this->env['year'], $this->env['month']);

        $oneAvailableCommandName = null;

        foreach (GameConst::$availableChiefCommand['전략'] as $commandType) {
            $cmd = buildNationCommandClass($commandType, $generalObj, $this->env, new LastTurn());
            $cmdName = $cmd->getName();
            $remainTurn = 0;
            $nextAvailableTurn = $cmd->getNextAvailableTurn();

            if ($nextAvailableTurn !== null && $currYearMonth < $nextAvailableTurn) {
                $remainTurn = $nextAvailableTurn - $currYearMonth;
            }
            else{
                $oneAvailableCommandName = $cmd->getRawClassName();
            }
            $availableCommandTypeList[$commandType] = ['name' => $cmdName, 'remainTurn' => $remainTurn];
        }

        $nationList = [];
        foreach (getAllNationStaticInfo() as $destNation) {
            $nationTarget = [
                'id' => $destNation['nation'],
                'name' => $destNation['name'],
                'color' => $destNation['color'],
                'power' => $destNation['power'],
            ];

            if($oneAvailableCommandName === null){
                $nationTarget['notAvailable'] = true;
            }
            else if ($destNation['nation'] == $nationID) {
                $nationTarget['notAvailable'] = true;
            }
            else {
                $testCommand = new static($generalObj, $this->env, $testTurn, [
                    'destNationID' => $destNation['nation'],
                    'commandType' => $oneAvailableCommandName
                ]);
                if (!$testCommand->hasFullConditionMet()) {
                    $nationTarget['notAvailable'] = true;
                }
            }

            $nationList[] = $nationTarget;
        }

        return [
            'procRes' => [
                'nationList' => $nationList,
                'startYear' => $this->env['startyear'],
                'delayCnt' => static::$delayCnt,
                'postReqTurn' => $this->getTargetPostReqTurn(),
                'availableCommandTypeList' => $availableCommandTypeList
            ],
        ];
    }
}
