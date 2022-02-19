<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_사기_춘화첩 extends \sammo\BaseItem{

    protected $rawName = '춘화첩';
    protected $name = '춘화첩(사기)';
    protected $info = '[전투] 사기 보정 +14';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'bonusAtmos'){
            return $value + 14;
        }
        return $value;
    }
}