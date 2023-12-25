<?php

namespace sammo\ActionBuff;

use sammo\General;
use \sammo\iAction;

class g65_내정성공 implements iAction
{
    use \sammo\DefaultAction;

    function onCalcDomestic(string $turnType, string $varType, float $value, $aux = null): float
    {
        if($turnType == '계략'){
            if($varType == 'success') return $value + 1;
        }
        return $value;
    }
}
