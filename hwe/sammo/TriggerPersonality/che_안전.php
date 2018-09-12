<?php
namespace sammo\TriggerPersonality;
use \sammo\iActionTrigger;
use \sammo\General;

class che_안전 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 9;
    static $name = '안전';
    static $info = '사기 -5, 징·모병 비용 -20%';

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost'){
                return $value * 0.8;
            }
        }

        return $value;
    }

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
        if($statName == 'bonusAtmos'){
            return $value - 5;
        }
        return $value;
    }
}