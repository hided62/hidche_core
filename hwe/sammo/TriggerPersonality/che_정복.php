<?php
namespace sammo\TriggerPersonality;
use \sammo\iActionTrigger;
use \sammo\General;

class che_정복 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 4;
    static $name = '정복';
    static $info = '명성 -10%, 사기 +5';

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
        if($statName == 'experience'){
            return $value * 0.9;
        }
        if($statName == 'bonusAtmos'){
            return $value + 5;
        }
        return $value;
    }
}