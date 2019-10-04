<?php
namespace sammo\ActionPersonality;
use \sammo\iAction;
use \sammo\General;

class che_출세 implements iAction{
    use \sammo\DefaultAction;

    protected $id = 6;
    protected $name = '출세';
    protected $info = '명성 +10%, 징·모병 비용 +20%';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost'){
                return $value * 1.2;
            }
        }

        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName == 'experience'){
            return $value * 1.1;
        }
        return $value;
    }
}