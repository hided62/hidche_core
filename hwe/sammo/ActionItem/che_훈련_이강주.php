<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_훈련_이강주 extends \sammo\BaseItem{

    protected static $id = 13;
    protected static $name = '이강주(훈련)';
    protected static $info = '[전투] 훈련 보정 +5';
    protected static $cost = 200;
    protected static $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'bonusTrain'){
            return $value + 5;
        }
        return $value;
    }
}