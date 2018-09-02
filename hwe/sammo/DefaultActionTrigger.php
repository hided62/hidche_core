<?php
namespace sammo;

trait DefaultActionTrigger{
    public function onPreTurnExecute(General $general, ?array $nation):array{
        return [];
    }
    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array{
        return [$score, $cost, $successRate, $failRate];
    }

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
        return $value;
    }
    
    public function onCalcStragicDelay(array $nation, int $commandType, int $turn):int{
        return $turn;
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