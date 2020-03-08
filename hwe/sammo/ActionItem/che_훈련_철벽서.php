<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_훈련_철벽서 extends \sammo\BaseItem{

    protected $id = 17;
    protected $rawName = '철벽서';
    protected $name = '철벽서(훈련)';
    protected $info = '[전투] 훈련 보정 +14';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'bonusTrain'){
            return $value + 14;
        }
        return $value;
    }
}