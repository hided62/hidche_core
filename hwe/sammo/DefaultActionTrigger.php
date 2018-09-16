<?php
namespace sammo;

trait DefaultActionTrigger{
    public function onPreTurnExecute(General $general):array{
        return [];
    }
    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        return $value;
    }

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
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
    public function getBattleInitSkillTriggerList(WarUnit $unit):array{
        return [];
    }
    public function getBattlePhaseSkillTriggerList(WarUnit $unit):array{
        return [];
    }
}