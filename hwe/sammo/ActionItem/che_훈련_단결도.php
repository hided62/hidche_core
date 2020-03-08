<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_훈련_단결도 extends \sammo\BaseItem{

    protected $id = 18;
    protected $rawName = '단결도';
    protected $name = '단결도(훈련)';
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