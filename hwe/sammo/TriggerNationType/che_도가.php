<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_도가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '도가';
    static $info = '';
    static $pros = '인구↑';
    static $cons = '기술↓ 치안↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if($turnType == '기술'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        else if($turnType == '치안'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        
        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'pop'){
            return $amount * 1.2;
        }
        
        return $amount;
    }
}