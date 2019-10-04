<?php
namespace sammo;

trait DefaultAction{

    public function getName():string{
        return $this->name;
    }

    public function getInfo():string{
        return $this->info;
    }

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        return null;
    }
    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        return $value;
    }

    public function onCalcStrategic(string $turnType, string $varType, $value){
        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        return $amount;
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        return [1, 1];
    }
    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return null;
    }
    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return null;
    }
}