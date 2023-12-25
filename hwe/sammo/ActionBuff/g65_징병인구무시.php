<?php

namespace sammo\ActionBuff;

use \sammo\iAction;

class g65_징병인구무시 implements iAction
{
    use \sammo\DefaultAction;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux = null): float
    {
        if ($turnType == '징집인구' && $varType == 'score') {
            return 0;
        }

        return $value;
    }
}
