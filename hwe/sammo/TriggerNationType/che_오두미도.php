<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_오두미도 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '오두미도';
    static $info = '';
    static $pros = '쌀수입↑ 인구↑';
    static $cons = '기술↓ 수성↓ 내정↓';


    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if($turnType == 'tech'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        else if($turnType == 'def' || $turnType == 'wall'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        else if($turnType == 'agri' || $turnType == 'comm'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        
        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'rice'){
            return $amount * 1.1;
        }
        if($type == 'pop'){
            return $amount * 1.2;
        }
        
        return $amount;
    }

}