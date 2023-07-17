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
    CityConst,
    Command,
    TimeUtil
};

use function \sammo\cutTurn;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\Enums\GeneralQueryMode;

class che_발령 extends Command\NationCommand
{
    static protected $actionName = '발령';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        //NOTE: 사망 직전에 턴을 넣을 수 있으므로, 존재하지 않는 장수여도 argTest에서 바로 탈락시키지 않음
        if (!key_exists('destGeneralID', $this->arg)) {
            return false;
        }
        if (!key_exists('destCityID', $this->arg)) {
            return false;
        }
        if (CityConst::byID($this->arg['destCityID']) === null) {
            return false;
        }
        $destGeneralID = $this->arg['destGeneralID'];
        $destCityID = $this->arg['destCityID'];

        $this->arg = [
            'destGeneralID' => $destGeneralID,
            'destCityID' => $destCityID,
        ];
        return true;
    }

    protected function init()
    {
        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        $this->minConditionConstraints = [
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
        ];
    }

    protected function initWithArg()
    {
        $this->setDestCity($this->arg['destCityID']);

        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['turntime'], GeneralQueryMode::Lite);
        $this->setDestGeneral($destGeneral);

        if ($this->arg['destGeneralID'] == $this->getGeneral()->getID()) {
            $this->fullConditionConstraints = [
                ConstraintHelper::AlwaysFail('본인입니다')
            ];
            return;
        }

        $this->fullConditionConstraints = [
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::FriendlyDestGeneral(),
            ConstraintHelper::OccupiedDestCity(),
            ConstraintHelper::SuppliedDestCity(),
        ];
    }

    public function getFailString(): string
    {
        $commandName = $this->getName();
        $failReason = $this->testFullConditionMet();
        if ($failReason === null) {
            throw new \RuntimeException('실행 가능한 커맨드에 대해 실패 이유를 수집');
        }
        $destGeneralName = $this->destGeneralObj->getName();
        return "{$failReason} <Y>{$destGeneralName}</> {$commandName} 실패.";
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
        $commandName = $this->getName();
        $destGeneralName = $this->destGeneralObj->getName();
        $destCityName = CityConst::byID($this->arg['destCityID'])->name;
        $josaRo = JosaUtil::pick($destCityName, '로');
        return "【{$destGeneralName}】【{$destCityName}】{$josaRo} {$commandName}";
    }


    public function run(\Sammo\RandUtil $rng): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $generalName = $general->getName();
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $destCity = $this->destCity;
        $destCityID = $destCity['city'];
        $destCityName = $destCity['name'];

        $destGeneral = $this->destGeneralObj;
        $destGeneralName = $destGeneral->getName();

        $logger = $general->getLogger();

        $destGeneral->setVar('city', $destCityID);

        $josaUl = JosaUtil::pick($destGeneralName, '을');
        $josaRo = JosaUtil::pick($destCityName, '로');
        $destGeneral->getLogger()->pushGeneralActionLog("<Y>{$generalName}</>에 의해 <G><b>{$destCityName}</b></>{$josaRo} 발령됐습니다. <1>$date</>");

        $yearMonth = Util::joinYearMonth($this->env['year'], $this->env['month']);
        if (cutTurn($general->getTurnTime(), $this->env['turnterm']) != cutTurn($destGeneral->getTurnTime(), $this->env['turnterm'])) {
            $yearMonth += 1;
        }
        $destGeneral->setAuxVar('last발령', $yearMonth);
        $logger->pushGeneralActionLog("<Y>{$destGeneralName}</>{$josaUl} <G><b>{$destCityName}</b></>{$josaRo} 발령했습니다. <1>$date</>");

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);
        $destGeneral->applyDB($db);

        return true;
    }

    public function exportJSVars(): array
    {
        $db = DB::db();
        $nationID = $this->getNationID();
        $troops = Util::convertArrayToDict($db->query('SELECT * FROM troop WHERE nation=%i', $nationID), 'troop_leader');
        $destRawGenerals = $db->queryAllLists('SELECT no,name,officer_level,npc,gold,rice,leadership,strength,intel,city,crew,train,atmos,troop FROM general WHERE nation = %i ORDER BY npc,binary(name)', $nationID);
        return [
            'procRes' => [
                'distanceList' => \sammo\JSCitiesBasedOnDistance($this->generalObj->getCityID(), 1),
                'cities' => \sammo\JSOptionsForCities(),
                'troops' => $troops,
                'generals' => $destRawGenerals,
                'generalsKey' => ['no', 'name', 'officerLevel', 'npc', 'gold', 'rice', 'leadership', 'strength', 'intel', 'cityID', 'crew', 'train', 'atmos', 'troopID']
            ]
        ];
    }
}
