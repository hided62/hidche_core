<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;

class che_종횡가 implements iAction{
    use \sammo\DefaultAction;

    protected $name = '종횡가';
    protected $info = '';
    static $pros = '전략↑ 수성↑';
    static $cons = '금수입↓ 내정↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '수비' || $turnType == '성벽'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }

        else if($turnType == '농업' || $turnType == '상업'){
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

    public function onCalcStrategic(string $turnType, string $varType, $value){
        if($varType == 'delay'){
            return Util::round($value / 2);
        }
        return $value;
    }
}