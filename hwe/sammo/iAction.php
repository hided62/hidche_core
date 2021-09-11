<?php
namespace sammo;

interface iAction{

    public function getName():string;
    public function getInfo():string;

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller;
    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float;

    public function onCalcStat(General $general, string $statName, $value, $aux=null);
    public function onCalcOpposeStat(General $general, string $statName, $value, $aux=null);
    public function onCalcStrategic(string $turnType, string $varType, $value);
    public function onCalcNationalIncome(string $type, $amount);

    public function getWarPowerMultiplier(WarUnit $unit):array;
    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller;
    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller;
    //NOTE: getBattleEndSkillTriggerList도 필요한가?
}