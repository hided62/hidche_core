<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_훈련_이강주 extends \sammo\BaseItem{

    protected $rawName = '이강주';
    protected $name = '이강주(훈련)';
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