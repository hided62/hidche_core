<?php
namespace sammo;

interface iAction{
    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller;
    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float;

    public function onPreGeneralStatUpdate(General $general, string $statName, $value);

    public function onCalcStat(General $general, string $stat, $value);
    public function onCalcStrategic(string $turnType, string $varType, $value);
    public function onCalcNationalIncome(string $type, int $amount):int;

    public function getWarPowerMultiplier(WarUnit $unit):array;
    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller;
    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller;
}