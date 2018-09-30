<?php
namespace sammo;

interface iAction{

    const PRIORITY_BEGIN = 50000;
    const PRIORITY_PRE   = 40000;
    const PRIORITY_BODY  = 30000;
    const PRIORITY_POST  = 20000;
    const PRIORITY_FINAL = 10000;

    //TODO: 능력치는?
    /**
     * @return iGeneralTrigger[]
     */
    public function getPreTurnExecuteTriggerList(General $general):array;
    public function onCalcDomestic(string $turnType, string $varType, float $value):float;

    public function onPreGeneralStatUpdate(General $general, string $statName, $value);

    public function onCalcStrategic(string $turnType, string $varType, $value);
    public function onCalcNationalIncome(string $type, int $amount):int;

    /**
     * @return iWarUnitTrigger[]
     */
    public function getWarPowerMultiplier(WarUnit $unit):array;
    /**
     * @return iWarUnitTrigger[]
     */
    public function getBattleInitSkillTriggerList(WarUnit $unit):array;
    /**
     * @return iWarUnitTrigger[]
     */
    public function getBattlePhaseSkillTriggerList(WarUnit $unit):array;
}