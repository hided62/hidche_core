<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_숙련_동작 extends \sammo\BaseItem{

    protected $rawName = '동작';
    protected $name = '동작(숙련)';
    protected $info = '숙련 +20%';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName == 'addDex'){
            return $value * 1.20;
        }
        return $value;
    }
}