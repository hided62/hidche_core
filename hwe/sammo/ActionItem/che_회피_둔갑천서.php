<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_회피_둔갑천서 extends \sammo\BaseItem{

    protected $id = 26;
    protected $name = '둔갑천서(회피)';
    protected $info = '[전투] 회피 확률 +20%p';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warAvoidRatio'){
            return $value + 0.2;
        }
        return $value;
    }
}