<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;

class che_불가 implements iAction{
    use \sammo\DefaultAction;

    static $name = '불가';
    static $info = '';
    static $pros = '민심↑ 수성↑';
    static $cons = '금수입↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if($turnType == '민심' || $turnType == '인구'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }
        else if($turnType == '수비' || $turnType == '성벽'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }
        
        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'gold'){
            return $amount * 0.9;
        }
        
        return $amount;
    }
}