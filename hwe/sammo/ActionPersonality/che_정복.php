<?php
namespace sammo\ActionPersonality;
use \sammo\iAction;
use \sammo\General;

class che_정복 implements iAction{
    use \sammo\DefaultAction;

    protected $id = 4;
    protected $name = '정복';
    protected $info = '명성 -10%, 사기 +5';

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName == 'experience'){
            return $value * 0.9;
        }
        if($statName == 'bonusAtmos'){
            return $value + 5;
        }
        return $value;
    }
}