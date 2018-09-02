<?php
namespace sammo;

interface iActionTrigger{

    const PRIORITY_BEGIN = 50000;
    const PRIORITY_PRE   = 40000;
    const PRIORITY_BODY  = 30000;
    const PRIORITY_POST  = 20000;
    const PRIORITY_FINAL = 10000;

    //TODO: 능력치는?
    public function onPreTurnExecute(General $general, ?array $nation):array;
    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array;
    public function onCalcSabotageProp(float $successRate):float;

    public function onPreGeneralStatUpdate(General $general, string $statName, $value);

    public function onCalcStragicDelay(array $nation, int $commandType, int $turn):int;
    public function onCalcNationalIncome(string $type, int $amount):int;

    public function getWarPowerMultiplier(WarUnit $unit):array;
    public function getBattleInitSkillTriggerList(WarUnit $unit):array;
    public function getBattlePhaseSkillTriggerList(WarUnit $unit):array;
}