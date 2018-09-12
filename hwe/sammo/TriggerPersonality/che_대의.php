<?php
namespace sammo\TriggerPersonality;
use \sammo\iActionTrigger;
use \sammo\General;

class che_대의 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 1;
    static $name = '대의';
    static $info = '명성 +10%, 훈련 -5';

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
        if($statName == 'experience'){
            return $value * 1.1;
        }
        if($statName == 'bonusTrain'){
            return $value - 5;
        }
        return $value;
    }
}