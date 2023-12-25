<?php

namespace sammo\ActionBuff;

use sammo\General;
use \sammo\iAction;

class g65_내정성공 implements iAction
{
    use \sammo\DefaultAction;

    static protected $target = ['상업', '농업', '치안', '기술', '인구', '성벽', '수비', '민심'];

    function onCalcDomestic(string $turnType, string $varType, float $value, $aux = null): float
    {
        if ($varType == 'success' && in_array($turnType, static::$target)) {
            return 1;
        }
        return $value;
    }
}
