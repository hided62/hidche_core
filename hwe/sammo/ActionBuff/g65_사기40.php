<?php

namespace sammo\ActionBuff;

use sammo\General;
use \sammo\iAction;

class g65_사기40 implements iAction
{
    use \sammo\DefaultAction;

    function onCalcStat(General $general, string $statName, $value, $aux = null)
    {
        if ($statName == 'bonusAtmos') {
            return $value + 40;
        }
        return $value;
    }
}
