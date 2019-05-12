<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_훈련_철벽서 extends \sammo\BaseItem{

    protected static $id = 17;
    protected static $name = '철벽서(훈련)';
    protected static $info = '[전투] 훈련 보정 +7';
    protected static $cost = 200;
    protected static $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'bonusTrain'){
            return $value + 7;
        }
        return $value;
    }
}