<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_불가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '불가';
    static $info = '';
    static $pros = '민심↑ 수성↑';
    static $cons = '금수입↓';

    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array{
        if($turnType == 'trust' || $turnType == 'pop'){
            $score *= 1.1;
            $cost *= 0.8;
        }
        else if($turnType == 'def' || $turnType == 'wall'){
            $score *= 1.1;
            $cost *= 0.8;
        }
        
        return [$score, $cost, $successRate, $failRate];
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'gold'){
            return $amount * 0.9;
        }
        
        return $amount;
    }
}