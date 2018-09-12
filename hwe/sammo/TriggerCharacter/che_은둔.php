<?php
namespace sammo\TriggerCharacter;
use \sammo\iActionTrigger;
use \sammo\General;

class che_대의 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 0;
    static $name = '왕좌';
    static $info = '명성 +10%, 사기 -5';

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if($turnType == '단련'){
            if($varType == 'succ'){
                return $value + 0.1;
            } 
        }

        return $value;
    }

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
        if($statName == 'bonusAtmos'){
            return $value - 5;
        }
        if($statName == 'bonusTrain'){
            return $value - 5;
        }
        if($statName == 'experience'){
            return $value * 0.9;
        }
        if($statName == 'dedication'){
            return $value * 0.9;
        }
        return $value;
    }
}