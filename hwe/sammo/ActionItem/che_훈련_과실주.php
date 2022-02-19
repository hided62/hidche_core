<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_훈련_과실주 extends \sammo\BaseItem{

    protected $rawName = '과실주';
    protected $name = '과실주(훈련)';
    protected $info = '[전투] 훈련 보정 +10';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'bonusTrain'){
            return $value + 10;
        }
        return $value;
    }
}

//NOTE: 구버전