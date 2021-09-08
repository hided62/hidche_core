<?php

namespace sammo\Command\Nation;

use \sammo\DB;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\General;
use \sammo\DummyGeneral;
use \sammo\ActionLogger;
use \sammo\GameConst;
use \sammo\LastTurn;
use \sammo\GameUnitConst;
use \sammo\Command;
use \sammo\Message;
use \sammo\MessageTarget;

use function \sammo\getAllNationStaticInfo;
use function \sammo\getNationStaticInfo;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_급습 extends Command\NationCommand
{
    static protected $actionName = '급습';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        //NOTE: 멸망 직전에 턴을 넣을 수 있으므로, 존재하지 않는 국가여도 argTest에서 바로 탈락시키지 않음
        if (!key_exists('destNationID', $this->arg)) {
            return false;
        }
        $destNationID = $this->arg['destNationID'];

        if (!is_int($destNationID)) {
            return false;
        }
        if ($destNationID < 1) {
            return false;
        }

        $this->arg = [
            'destNationID' => $destNationID
        ];
        return true;
    }

    protected function init()
    {
        $general = $this->generalObj;

        $env = $this->env;

        $this->setCity();
        $this->setNation(['strategic_cmd_limit']);



        $this->minConditionConstraints = [
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::AvailableStrategicCommand(),
        ];
    }

    protected function initWithArg()
    {
        $this->setDestNation($this->arg['destNationID'], null);

        $this->fullConditionConstraints = [
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::AllowDiplomacyWithTerm(
                1,
                12,
                '선포 12개월 이상인 상대국에만 가능합니다.'
            ),
            ConstraintHelper::AvailableStrategicCommand(),
        ];
    }

    public function getCommandDetailTitle(): string
    {
        $name = $this->getName();
        $reqTurn = $this->getPreReqTurn() + 1;
        $postReqTurn = $this->getPostReqTurn();

        return "{$name}/{$reqTurn}턴(재사용 대기 $postReqTurn)";
    }

    public function getCost(): array
    {
        return [0, 0];
    }

    public function getPreReqTurn(): int
    {
        return 0;
    }

    public function getPostReqTurn(): int
    {
        $genCount = Util::valueFit($this->nation['gennum'], GameConst::$initialNationGenLimit);
        $nextTerm = Util::round(sqrt($genCount * 16) * 10);

        $nextTerm = $this->generalObj->onCalcStrategic($this->getName(), 'delay', $nextTerm);
        return $nextTerm;
    }

    public function getBrief(): string
    {
        $commandName = $this->getName();
        $destNationName = getNationStaticInfo($this->arg['destNationID'])['name'];
        return "【{$destNationName}】에 {$commandName}";
    }


    public function run(): bool
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

        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("{$commandName} 발동! <1>$date</>");

        $general->addExperience(5 * ($this->getPreReqTurn() + 1));
        $general->addDedication(5 * ($this->getPreReqTurn() + 1));

        $broadcastMessage = "<Y>{$generalName}</>{$josaYi} <G><b>{$destNationName}</b></>에 <M>{$commandName}</>{$josaUl} 발동하였습니다.";

        $nationGeneralList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no != %i', $nationID, $generalID);
        foreach ($nationGeneralList as $nationGeneralID) {
            $nationGeneralLogger = new ActionLogger($nationGeneralID, $nationID, $year, $month);
            $nationGeneralLogger->pushGeneralActionLog($broadcastMessage, ActionLogger::PLAIN);
            $nationGeneralLogger->flush();
        }

        $josaYiCommand = JosaUtil::pick($commandName, '이');

        $broadcastMessage = "아국에 <M>{$commandName}</>{$josaYiCommand} 발동되었습니다.";

        $destNationGeneralList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no != %i', $nationID, $generalID);
        foreach ($destNationGeneralList as $destNationGeneralID) {
            $destNationGeneralLogger = new ActionLogger($destNationGeneralID, $destNationID, $year, $month);
            $destNationGeneralLogger->pushGeneralActionLog($broadcastMessage, ActionLogger::PLAIN);
            $destNationGeneralLogger->flush();
        }

        $destNationLogger = new ActionLogger(0, $destNationID, $year, $month);
        $destNationLogger->pushNationalHistoryLog("<D><b>{$nationName}</b></>의 <Y>{$generalName}</>{$josaYi} 아국에 <M>{$commandName}</>{$josaUl} 발동");
        $destNationLogger->flush();

        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <D><b>{$destNationName}</b></>에 <M>{$commandName}</>{$josaUl} 발동");

        $db->update('nation', [
            'strategic_cmd_limit' => $this->generalObj->onCalcStrategic($this->getName(), 'globalDelay', 9)
        ], 'nation=%i', $nationID);
        $db->update('diplomacy', [
            'term' => $db->sqleval('`term` - %i', 3),
        ], '(me = %i AND you = %i) OR (you = %i AND me = %i)', $nationID, $destNationID, $nationID, $destNationID);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);

        return true;
    }

    public function getJSPlugins(): array
    {
        return [
            'defaultSelectNationByMap'
        ];
    }

    public function getForm(): string
    {
        $generalObj = $this->generalObj;
        $nationID = $generalObj->getNationID();
        $nationList = [];
        $testTurn = new LastTurn($this->getName(), null, $this->getPreReqTurn());
        foreach (getAllNationStaticInfo() as $destNation) {
            if ($destNation['nation'] == $nationID) {
                continue;
            }

            $testTurn->setArg(['destNationID' => $destNation['nation']]);
            $testCommand = new static($generalObj, $this->env, $testTurn, ['destNationID' => $destNation['nation']]);
            if ($testCommand->hasFullConditionMet()) {
                $destNation['availableCommand'] = true;
            } else {
                $destNation['availableCommand'] = false;
            }

            $nationList[] = $destNation;
        }

        ob_start();
?>
        <?= \sammo\getMapHtml() ?><br>
        선택된 국가에 급습을 발동합니다.<br>
        선포, 전쟁중인 상대국에만 가능합니다.<br>
        상대 국가를 목록에서 선택하세요.<br>
        배경색은 현재 급습 불가능 국가는 <font color=red>붉은색</font>으로 표시됩니다.<br>
        <select class='formInput' name="destNationID" id="destNationID" size='1' style='color:white;background-color:black;'>
            <?php foreach ($nationList as $nation) : ?>
                <option value='<?= $nation['nation'] ?>' style='color:<?= $nation['color'] ?>;<?= $nation['availableCommand'] ? '' : 'background-color:red;' ?>'>【<?= $nation['name'] ?> 】</option>
            <?php endforeach; ?>
            <input type=button id="commonSubmit" value="<?= $this->getName() ?>">
    <?php
        return ob_get_clean();
    }
}
