<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_덕가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '덕가';
    static $info = '';
    static $pros = '치안↑ 인구↑ 민심↑';
    static $cons = '쌀수입↓ 수성↓';

    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array{
        if($turnType == 'secu'){
            $score *= 1.1;
            $cost *= 0.8;
        }
        else if($turnType == 'trust' || $turnType == 'pop'){
            $score *= 1.1;
            $cost *= 0.8;
        }

        else if($turnType == 'def' || $turnType == 'wall'){
            $score *= 0.9;
            $cost *= 1.2;
        }
        
        return [$score, $cost, $successRate, $failRate];
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'rice'){
            return $amount * 0.9;
        }
        if($type == 'pop'){
            return $amount * 1.2;
        }
        
        return $amount;
    }
}