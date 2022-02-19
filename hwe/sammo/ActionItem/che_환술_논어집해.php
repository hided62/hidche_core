<?php

namespace sammo\ActionItem;

use \sammo\General;

class che_환술_논어집해 extends \sammo\BaseItem
{
    protected $rawName = '논어집해';
    protected $name = '논어집해(환술)';
    protected $info = '[전투] 계략 성공 확률 +10%p, 계략 성공 시 대미지 +30%, 공격 시 페이즈 -1';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux = null)
    {
        if ($statName === 'initWarPhase') {
            return $value - 1;
        }
        if ($statName === 'warMagicSuccessProb') {
            return $value + 0.1;
        }
        if ($statName === 'warMagicSuccessDamage') {
            return $value * 1.3;
        }
        return $value;
    }
}
