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
use sammo\StaticEventHandler;

class che_부대탈퇴지시 extends Command\NationCommand
{
    static protected $actionName = '부대 탈퇴 지시';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        if (!key_exists('destGeneralID', $this->arg)) {
            return false;
        }
        $destGeneralID = $this->arg['destGeneralID'];
        if (!is_int($destGeneralID)) {
            return false;
        }
        if ($destGeneralID <= 0) {
            return false;
        }
        $this->arg = [
            'destGeneralID' => $destGeneralID
        ];
        return true;
    }

    protected function init()
    {
        $this->setCity();
        $this->setNation();

        $this->minConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::BeChief(),
        ];
    }

    public function getBrief(): string
    {
        $commandName = $this->getName();
        $destGeneralName = $this->destGeneralObj->getName();
        return "【{$destGeneralName}】{$commandName}";
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
            ConstraintHelper::BeChief(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::FriendlyDestGeneral()
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

    public function run(\Sammo\RandUtil $rng): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $generalName = $general->getName();

        $destGeneral = $this->destGeneralObj;
        $destGeneralName = $destGeneral->getName();
        $logger = $this->getLogger();

        $troopID = $destGeneral->getVar('troop');
        if($troopID == 0){
            $josaUn = JosaUtil::pick($destGeneralName, '은');
            $logger->pushGeneralActionLog("<Y>{$destGeneralName}</>{$josaUn} 부대원이 아닙니다.");
            $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
            return true;
        }

        if($troopID == $destGeneral->getID()){
            $josaUn = JosaUtil::pick($destGeneralName, '은');
            $logger->pushGeneralActionLog("<Y>{$destGeneralName}</>{$josaUn} 부대장입니다.");
            $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
            return true;
        }

        $destGeneral->setVar('troop', 0);

        $logger->pushGeneralActionLog("<Y>{$destGeneralName}</>에게 부대 탈퇴를 지시했습니다.");
        $destGeneral->getLogger()->pushGeneralActionLog("<Y>{$generalName}</>에게 부대 탈퇴를 지시 받았습니다.");
        
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        StaticEventHandler::handleEvent($this->generalObj, $this->destGeneralObj, $this::class, $this->env, $this->arg ?? []);
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
            ]
        ];
    }
}
