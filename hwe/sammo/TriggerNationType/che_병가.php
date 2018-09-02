<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_병가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '병가';
    static $info = '';
    static $pros = '기술↑ 수성↑';
    static $cons = '인구↓ 민심↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if($turnType == 'tech'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }
        else if($turnType == 'def' || $turnType == 'wall'){
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
        if($type == 'pop'){
            return $amount * 0.8;
        }
        
        return $amount;
    }
}