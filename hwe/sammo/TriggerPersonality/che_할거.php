<?php
namespace sammo\TriggerPersonality;
use \sammo\iActionTrigger;
use \sammo\General;

class che_할거 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 5;
    static $name = '할거';
    static $info = '명성 -10%, 훈련 +5';

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
        if($statName == 'experience'){
            return $value * 0.9;
        }
        if($statName == 'bonusTrain'){
            return $value + 5;
        }
        return $value;
    }
}