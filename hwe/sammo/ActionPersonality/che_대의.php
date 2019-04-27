<?php
namespace sammo\ActionPersonality;
use \sammo\iAction;
use \sammo\General;

class che_대의 implements iAction{
    use \sammo\DefaultAction;

    static $id = 1;
    static $name = '대의';
    static $info = '명성 +10%, 훈련 -5';

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName == 'experience'){
            return $value * 1.1;
        }
        if($statName == 'bonusTrain'){
            return $value - 5;
        }
        return $value;
    }
}