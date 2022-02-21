<?php

namespace sammo\ActionItem;

use \sammo\General;

class che_집중_전국책 extends \sammo\BaseItem
{
    protected $rawName = '전국책';
    protected $name = '전국책(집중)';
    protected $info = '[전투] 계략 성공 시 대미지 +30%';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux = null)
    {
        if ($statName === 'warMagicSuccessDamage') {
            return $value * 1.3;
        }
        return $value;
    }
}
