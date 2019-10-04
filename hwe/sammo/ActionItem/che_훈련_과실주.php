<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_훈련_과실주 extends \sammo\BaseItem{

    protected static $id = 12;
    protected static $name = '과실주(훈련)';
    protected static $info = '[전투] 훈련 보정 +10';
    protected static $cost = 200;
    protected static $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'bonusTrain'){
            return $value + 10;
        }
        return $value;
    }
}