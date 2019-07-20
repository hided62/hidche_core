<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_훈련_단결도 extends \sammo\BaseItem{

    protected static $id = 18;
    protected static $name = '단결도(훈련)';
    protected static $info = '[전투] 훈련 보정 +14';
    protected static $cost = 200;
    protected static $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'bonusTrain'){
            return $value + 14;
        }
        return $value;
    }
}