<?php

namespace sammo\ActionScenarioEffect;

use \sammo\iAction;
use sammo\Util;
use sammo\WarUnit;
use sammo\WarUnitCity;
use sammo\WarUnitTrigger\che_전멸시페이즈증가;
use sammo\WarUnitTriggerCaller;

class event_MoreEffect implements iAction
{
    use \sammo\DefaultAction;

    public function getWarPowerMultiplier(WarUnit $unit): array
    {
        if ($unit->isAttacker()) {
            return [1.4, 0.7143];
        }
        return [1, 1];
    }

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux = null): float
    {
        $scoreMap = [
            '상업' => 2,
            '농업' => 2,
            '치안' => 2,
            '기술' => 2,
            '성벽' => 2,
            '수비' => 2,
            '인구' => 2,
            '민심' => 2,
        ];
        if ($turnType == 'changeDefenceTrain') {
            return 0;
        }
        if ($varType === 'score' && key_exists($turnType, $scoreMap)) {
            $multiplier = $scoreMap[$turnType];
            $value *= $multiplier;
            return $value;
        }

        return $value;
    }

    public function onCalcNationalIncome(string $type, $amount){
        if($type == 'gold'){
            return $amount * 2;
        }
        if($type == 'rice'){
            return $amount * 2;
        }
        if($type == 'pop' && $amount > 0){
            return $amount * 2;
        }
        
        return $amount;
    }

    public function getBattlePhaseSkillTriggerList(\sammo\WarUnit $unit): ?WarUnitTriggerCaller
    {
        return new WarUnitTriggerCaller(
            new che_전멸시페이즈증가($unit),
        );
    }
}
