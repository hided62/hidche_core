<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_회피저지_노군입산부 extends \sammo\BaseItem{

    protected $rawName = '노군입산부';
    protected $name = '노군입산부(회피저지)';
    protected $info = '[전투] 상대 회피 확률 -15%p';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcOpposeStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warAvoidRatio'){
            return $value - 0.15;
        }
        return $value;
    }
}