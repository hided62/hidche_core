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

    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array{
        if($turnType == 'secu'){
            $score *= 0.9;
            $cost *= 1.2;
        }
        else if($turnType == 'trust' || $turnType == 'pop'){
            $score *= 0.9;
            $cost *= 1.2;
        }
        
        return [$score, $cost, $successRate, $failRate];
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