<?php

namespace sammo\ActionBuff;

use \sammo\iAction;

class g65_징병비용무시 implements iAction
{
    use \sammo\DefaultAction;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux = null): float
    {
        if ($varType == 'cost' && in_array($turnType, ['징병', '모병'])) {
            return 0;
        }

        return $value;
    }
}
