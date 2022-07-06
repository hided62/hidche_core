<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_간파_노군입산부 extends \sammo\BaseItem{

    protected $rawName = '노군입산부';
    protected $name = '노군입산부(간파)';
    protected $info = '[전투] 상대 회피 확률 -25%p, 상대 필살 확률 -10%p';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcOpposeStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warAvoidRatio'){
            return $value - 0.25;
        }
        if($statName === 'warCriticalRatio'){
            return $value - 0.10;
        }
        return $value;
    }
}