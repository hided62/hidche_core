<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_법가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '법가';
    static $info = '';
    static $pros = '금수입↑ 치안↑';
    static $cons = '인구↓ 민심↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if($turnType == 'secu'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }

        else if($turnType == 'trust' || $turnType == 'pop'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        } 
        
        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'gold'){
            return $amount * 1.1;
        }
        if($type == 'pop'){
            return $amount * 0.8;
        }
        
        return $amount;
    }
}