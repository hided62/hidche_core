<?php
namespace sammo\ActionPersonality;
use \sammo\iAction;
use \sammo\General;

class che_안전 implements iAction{
    use \sammo\DefaultAction;

    protected $id = 9;
    protected $name = '안전';
    protected $info = '사기 -5, 징·모병 비용 -20%';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost'){
                return $value * 0.8;
            }
        }

        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName == 'bonusAtmos'){
            return $value - 5;
        }
        return $value;
    }
}