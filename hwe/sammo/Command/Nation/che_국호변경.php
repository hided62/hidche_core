<?php

namespace sammo\Command\Nation;

use \sammo\DB;
use \sammo\Util;
use \sammo\Json;
use \sammo\JosaUtil;
use \sammo\General;
use \sammo\DummyGeneral;
use \sammo\ActionLogger;
use \sammo\GameConst;
use \sammo\LastTurn;
use \sammo\GameUnitConst;
use \sammo\Command;
use \sammo\MessageTarget;
use \sammo\Message;
use \sammo\CityConst;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\Enums\InheritanceKey;
use sammo\Event\Action;

class che_국호변경 extends Command\NationCommand
{
    static protected $actionName = '국호변경';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }

        if (!key_exists('nationName', $this->arg)) {
            return false;
        }
        $nationName = $this->arg['nationName'];
        if (!is_string($nationName)) {
            return false;
        }
        if (mb_strwidth($nationName) > 18 || $nationName == '') {
            return false;
        }

        $this->arg = [
            'nationName' => $nationName,
        ];
        return true;
    }

    protected function init()
    {
        $general = $this->generalObj;

        $env = $this->env;

        $this->setCity();
        $this->setNation(['aux']);
        $actionName = $this->getName();

        $this->minConditionConstraints = [
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ReqNationAuxValue("can_{$actionName}", 0, '>', 0, '더이상 변경이 불가능합니다.')
        ];
    }

    protected function initWithArg()
    {
        $actionName = $this->getName();

        $this->fullConditionConstraints = [
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ReqNationAuxValue("can_{$actionName}", 0, '>', 0, '더이상 변경이 불가능합니다.')
        ];
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
        return 0;
    }

    public function getBrief(): string
    {
        $newNationName = $this->arg['nationName'];
        $josaRo = JosaUtil::pick($newNationName, '로');
        return "국호를 【{$newNationName}】{$josaRo} 변경";
    }

    public function run(\Sammo\RandUtil $rng): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $actionName = $this->getName();

        $general = $this->generalObj;
        $generalID = $general->getID();
        $generalName = $general->getName();
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $nationID = $general->getNationID();
        $nationName = $this->nation['name'];

        $logger = $general->getLogger();

        $newNationName = $this->arg['nationName'];

        if($db->queryFirstField('SELECT name FROM nation WHERE name = %s LIMIT 1', $newNationName)){
            $text = "이미 같은 국호를 가진 곳이 있습니다. {$actionName} 실패 <1>{$date}</>";
            $general->getLogger()->pushGeneralActionLog($text);
            return false;
        }


        $josaRo = JosaUtil::pick($newNationName, '로');

        $general->addExperience(5 * ($this->getPreReqTurn() + 1));
        $general->addDedication(5 * ($this->getPreReqTurn() + 1));

        $josaYi = JosaUtil::pick($generalName, '이');
        $josaYiNation = JosaUtil::pick($nationName, '이');

        $aux = Json::decode($this->nation['aux']);
        $aux["can_{$actionName}"] = 0;

        $db->update('nation', [
            'name'=>$newNationName,
            'aux'=>Json::encode($aux)
        ], 'nation=%i', $nationID);

        $logger->pushGeneralActionLog("국호를 <D><b>{$newNationName}</b></>{$josaRo} 변경합니다. <1>$date</>");
        $logger->pushGeneralHistoryLog("국호를 <D><b>{$newNationName}</b></>{$josaRo} 변경");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} 국호를 <D><b>{$newNationName}</b></>{$josaRo} 변경");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} 국호를 <D><b>{$newNationName}</b></>{$josaRo} 변경합니다.");
        $logger->pushGlobalHistoryLog("<S><b>【국호변경】</b></><D><b>{$nationName}</b></>{$josaYiNation} 국호를 <D><b>{$newNationName}</b></>{$josaRo} 변경합니다.");

        $general->increaseInheritancePoint(InheritanceKey::active_action, 1);
        $this->setResultTurn(new LastTurn($this->getName(), $this->arg, 0));
        $general->applyDB($db);
        return true;
    }
}
