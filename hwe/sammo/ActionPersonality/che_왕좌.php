<?php
namespace sammo\ActionPersonality;
use \sammo\iAction;
use \sammo\General;

class che_왕좌 implements iAction{
    use \sammo\DefaultAction;

    protected $id = 0;
    protected $name = '왕좌';
    protected $info = '명성 +10%, 사기 -5';

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName == 'experience'){
            return $value * 1.1;
        }
        if($statName == 'bonusAtmos'){
            return $value - 5;
        }
        return $value;
    }
}