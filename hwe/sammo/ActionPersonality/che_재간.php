<?php
namespace sammo\ActionPersonality;
use \sammo\iAction;
use \sammo\General;

class che_재간 implements iAction{
    use \sammo\DefaultAction;

    static $id = 7;
    static $name = '재간';
    static $info = '명성 -10%, 징·모병 비용 -20%';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost'){
                return $value * 0.8;
            }
        }

        return $value;
    }

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
        if($statName == 'experience'){
            return $value * 0.9;
        }
        return $value;
    }
}