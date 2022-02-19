<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_회피_태평요술 extends \sammo\BaseItem{

    protected $rawName = '태평요술';
    protected $name = '태평요술(회피)';
    protected $info = '[전투] 회피 확률 +15%p';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warAvoidRatio'){
            return $value + 0.15;
        }
        return $value;
    }
}