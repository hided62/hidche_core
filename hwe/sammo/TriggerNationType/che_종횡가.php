<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_종횡가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '종횡가';
    static $info = '';
    static $pros = '전략↑ 수성↑';
    static $cons = '금수입↓ 내정↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
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

    public function onCalcStragicDelay(array $nation, int $commandType, int $turn):int{
        return Util::round($turn / 2);
    }
}