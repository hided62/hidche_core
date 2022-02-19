<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_사기_초선화 extends \sammo\BaseItem{

    protected $rawName = '초선화';
    protected $name = '초선화(사기)';
    protected $info = '[전투] 사기 보정 +15';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'bonusAtmos'){
            return $value + 15;
        }
        return $value;
    }
}