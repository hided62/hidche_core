<?php
namespace sammo\ActionPersonality;
use \sammo\iAction;
use \sammo\General;

class che_의협 implements iAction{
    use \sammo\DefaultAction;

    static $id = 2;
    static $name = '의협';
    static $info = '사기 +5, 징·모병 비용 +20%';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost'){
                return $value * 1.2;
            }
        }

        return $value;
    }

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
        if($statName == 'bonusAtmos'){
            return $value + 5;
        }
        return $value;
    }
}