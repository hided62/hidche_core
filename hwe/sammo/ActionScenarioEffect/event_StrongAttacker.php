<?php

namespace sammo\ActionScenarioEffect;

use \sammo\iAction;
use sammo\WarUnit;
use sammo\WarUnitCity;

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
}
