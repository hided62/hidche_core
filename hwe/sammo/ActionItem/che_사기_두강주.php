<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_사기_의적주 extends \sammo\BaseItem{

    protected $id = 15;
    protected $name = '두강주(사기)';
    protected $info = '[전투] 사기 보정 +10';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'bonusAtmos'){
            return $value + 10;
        }
        return $value;
    }
}