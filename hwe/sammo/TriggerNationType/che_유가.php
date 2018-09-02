<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_유가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '유가';
    static $info = '';
    static $pros = '내정↑ 민심↑';
    static $cons = '쌀수입↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if($turnType == 'agri' || $turnType == 'comm'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }
        else if($turnType == 'trust' || $turnType == 'pop'){
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