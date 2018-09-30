<?php
namespace sammo\ActionPersonality;
use \sammo\iAction;
use \sammo\General;

class che_출세 implements iAction{
    use \sammo\DefaultAction;

    static $id = 6;
    static $name = '출세';
    static $info = '명성 +10%, 징·모병 비용 +20%';

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost'){
                return $value * 1.2;
            }
        }

        return $value;
    }

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
        if($statName == 'experience'){
            return $value * 1.1;
        }
        return $value;
    }
}