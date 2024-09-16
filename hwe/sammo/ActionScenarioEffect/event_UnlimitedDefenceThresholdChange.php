<?php

namespace sammo\ActionScenarioEffect;

use \sammo\iAction;

class event_UnlimitedDefenceThresholdChange implements iAction
{
    use \sammo\DefaultAction;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux = null): float
    {
        if ($turnType == 'changeDefenceTrain') {
            return 0;
        }
        return $value;
    }
}
