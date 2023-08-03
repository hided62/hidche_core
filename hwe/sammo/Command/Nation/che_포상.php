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

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\Enums\GeneralQueryMode;

class che_포상 extends Command\NationCommand
{
    static protected $actionName = '포상';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        //NOTE: 사망 직전에 '포상' 턴을 넣을 수 있으므로, 존재하지 않는 장수여도 argTest에서 바로 탈락시키지 않음
        if (!key_exists('isGold', $this->arg)) {
            return false;
        }
        if (!key_exists('amount', $this->arg)) {
            return false;
        }
        if (!key_exists('destGeneralID', $this->arg)) {
            return false;
        }
        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $destGeneralID = $this->arg['destGeneralID'];
        if (!is_numeric($amount)) {
            return false;
        }
        $amount = Util::round($amount, -2);
        $amount = Util::valueFit($amount, 100, GameConst::$maxResourceActionAmount);
        if ($amount <= 0) {
            return false;
        }
        if (!is_bool($isGold)) {
            return false;
        }
        if (!is_int($destGeneralID)) {
            return false;
        }
        if ($destGeneralID <= 0) {
            return false;
        }
        $this->arg = [
            'isGold' => $isGold,
            'amount' => $amount,
            'destGeneralID' => $destGeneralID
        ];
        return true;
    }

    protected function init()
    {
        $general = $this->generalObj;

        $this->setCity();
        $this->setNation(['gold', 'rice']);

        $this->minConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
        ];
    }

    protected function initWithArg()
    {
        $destGeneral = General::createObjFromDB($this->arg['destGeneralID']);
        $this->setDestGeneral($destGeneral);

        if($this->arg['destGeneralID'] == $this->getGeneral()->getID()){
            $this->fullConditionConstraints=[
                ConstraintHelper::AlwaysFail('본인입니다')
            ];
            return;
        }

        $this->fullConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::FriendlyDestGeneral()
        ];
        if ($this->arg['isGold']) {
            $this->fullConditionConstraints[] = ConstraintHelper::ReqNationGold(1 + GameConst::$basegold);
        } else {
            $this->fullConditionConstraints[] = ConstraintHelper::ReqNationRice(1 + GameConst::$baserice);
        }
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
        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $amountText = number_format($amount, 0);
        $resName = $isGold ? '금' : '쌀';
        $destGeneral = $this->destGeneralObj;
        $commandName = $this->getName();
        return "【{$destGeneral->getName()}】 {$resName} $amountText {$commandName}";
    }


    public function run(\Sammo\RandUtil $rng): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $nation = $this->nation;
        $nationID = $nation['nation'];

        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $resKey = $isGold ? 'gold' : 'rice';
        $resName = $isGold ? '금' : '쌀';
        $destGeneral = $this->destGeneralObj;

        $amount = Util::valueFit(
            $amount,
            0,
            $nation[$resKey] - ($isGold ? GameConst::$basegold : GameConst::$baserice)
        );
        $amountText = number_format($amount, 0);

        $logger = $general->getLogger();

        $destGeneral->increaseVar($resKey, $amount);
        $db->update('nation', [
            $resKey => $db->sqleval('%b - %i', $resKey, $amount)
        ], 'nation=%i', $nationID);

        $josaUl = JosaUtil::pick($amountText, '을');

        $destGeneral->getLogger()->pushGeneralActionLog("{$resName} <C>{$amountText}</>{$josaUl} 포상으로 받았습니다.", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("<Y>{$destGeneral->getName()}</>에게 {$resName} <C>$amountText</>{$josaUl} 수여했습니다. <1>$date</>");

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
                'troops' => $troops,
                'generals' => $destRawGenerals,
                'generalsKey' => ['no', 'name', 'officerLevel', 'npc', 'gold', 'rice', 'leadership', 'strength', 'intel', 'cityID', 'crew', 'train', 'atmos', 'troopID'],
                'cities' => \sammo\JSOptionsForCities(),
                'minAmount' => 100,
                'maxAmount' => GameConst::$maxResourceActionAmount,
                'amountGuide' => GameConst::$resourceActionAmountGuide,
            ]
        ];
    }
}
