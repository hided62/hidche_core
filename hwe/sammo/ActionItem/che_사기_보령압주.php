<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_사기_보령압주 extends \sammo\BaseItem{

    protected static $id = 16;
    protected static $name = '보령압주(사기)';
    protected static $info = '[전투] 사기 보정 +5';
    protected static $cost = 200;
    protected static $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'bonusAtmos'){
            return $value + 5;
        }
        return $value;
    }
}