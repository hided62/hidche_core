<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_필살_둔갑천서 extends \sammo\BaseItem{

    protected $rawName = '둔갑천서';
    protected $name = '둔갑천서(필살)';
    protected $info = '[전투] 필살 확률 +20%p';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warCriticalRatio'){
            return $value + 0.20;
        }
        return $value;
    }
}