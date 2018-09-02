<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_음양가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '음양가';
    static $info = '';
    static $pros = '내정↑ 인구↑';
    static $cons = '기술↓ 전략↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if($turnType == 'agri' || $turnType == 'comm'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }

        else if($turnType == 'tech'){
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

    public function onCalcStragicDelay(array $nation, int $commandType, int $turn):int{
        return $turn * 2;
    }
}