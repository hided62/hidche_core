<?php
namespace sammo\TriggerPersonality;
use \sammo\iActionTrigger;
use \sammo\General;

class che_패권 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 3;
    static $name = '패권';
    static $info = '훈련 +5, 징·모병 비용 +20%';

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost'){
                return $value * 1.2;
            }
        }

        return $value;
    }

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
        if($statName == 'bonusTrain'){
            return $value + 5;
        }
        return $value;
    }
}