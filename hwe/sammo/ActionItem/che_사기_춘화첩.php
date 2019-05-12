<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_사기_춘화첩 extends \sammo\BaseItem{

    protected static $id = 19;
    protected static $name = '춘화첩(사기)';
    protected static $info = '[전투] 사기 보정 +7';
    protected static $cost = 200;
    protected static $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'bonusAtmos'){
            return $value + 7;
        }
        return $value;
    }
}