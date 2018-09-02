<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_도적 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '도적';
    static $info = '';
    static $pros = '계략↑';
    static $cons = '금수입↓ 치안↓ 민심↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if($turnType == 'secu'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        else if($turnType == 'trust' || $turnType == 'pop'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        
        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'gold'){
            return $amount * 0.9;
        }
        
        return $amount;
    }

    public function onCalcSabotageProp(float $successRate):float{
        return $successRate + 0.1;
    }
}