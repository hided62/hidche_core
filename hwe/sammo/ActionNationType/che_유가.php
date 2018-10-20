<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;

class che_유가 implements iAction{
    use \sammo\DefaultAction;

    static $name = '유가';
    static $info = '';
    static $pros = '내정↑ 민심↑';
    static $cons = '쌀수입↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '농업' || $turnType == '상업'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }
        else if($turnType == '민심' || $turnType == '인구'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }
        
        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'rice'){
            return $amount * 0.9;
        }
        
        return $amount;
    }
}