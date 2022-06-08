<?php

namespace sammo;

use sammo\Enums\InheritanceKey;

class DummyGeneral extends General{
    public function __construct(bool $initLogger=true){
        $raw = [
            'no'=>0,
            'name'=>'Dummy',
            'npc'=>3,
            'city'=>0,
            'nation'=>0,
            'officer_level'=>0,
            'crewtype'=>-1,
            'turntime'=>'2012-03-04 05:06:07.000000',
            'experience'=>0,
            'dedication'=>0,
            'gold'=>0,
            'rice'=>0,
            'leadership'=>10,
            'strength'=>10,
            'intel'=>10,
        ];

        $this->raw = $raw;

        $this->resultTurn = new LastTurn();

        if($initLogger){
            $this->initLogger(1, 1);
        }
    }

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller();
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller();
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        return $value;
    }

    public function onCalcOpposeStat(General $general, string $statName, $value, $aux=null){
        return $value;
    }

    public function getInheritancePoint(InheritanceKey $key, &$aux = null, bool $forceCalc = false): int|float{
        return 0;
    }

    public function setInheritancePoint(InheritanceKey $key, $value, $aux = null){
        return;
    }

    public function increaseInheritancePoint(InheritanceKey $key, $value, $aux = null){
        return;
    }

    function applyDB($db):bool{
        if($this->logger){
            $this->initLogger($this->logger->getYear(), $this->logger->getMonth());
        }
        return true;
    }
}