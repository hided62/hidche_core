<?php

namespace sammo\ActionScenarioEffect;

use \sammo\iAction;
use sammo\WarUnit;
use sammo\WarUnitCity;
use sammo\WarUnitTrigger\che_전멸시페이즈증가;
use sammo\WarUnitTriggerCaller;

class event_StrongAttacker implements iAction
{
    use \sammo\DefaultAction;

    public function getWarPowerMultiplier(WarUnit $unit): array
    {
        if ($unit instanceof WarUnitCity) {
            return [1, 1];
        }
        if ($unit->getOppose() instanceof WarUnitCity) {
            return [1, 1];
        }

        if ($unit->isAttacker()) {
            return [1.4, 0.7143];
        }
        return [1, 1];
    }

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux = null): float
    {
        if ($turnType == 'changeDefenceTrain') {
            return 0;
        }
        return $value;
    }

    public function getBattlePhaseSkillTriggerList(\sammo\WarUnit $unit): ?WarUnitTriggerCaller
    {
        return new WarUnitTriggerCaller(
            new che_전멸시페이즈증가($unit),
        );
    }
}
