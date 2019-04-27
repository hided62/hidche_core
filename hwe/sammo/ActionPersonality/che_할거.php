<?php
namespace sammo\ActionPersonality;
use \sammo\iAction;
use \sammo\General;

class che_할거 implements iAction{
    use \sammo\DefaultAction;

    static $id = 5;
    static $name = '할거';
    static $info = '명성 -10%, 훈련 +5';

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName == 'experience'){
            return $value * 0.9;
        }
        if($statName == 'bonusTrain'){
            return $value + 5;
        }
        return $value;
    }
}