<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_법가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '법가';
    static $info = '';
    static $pros = '금수입↑ 치안↑';
    static $cons = '인구↓ 민심↓';

    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array{
        if($turnType == 'secu'){
            $score *= 1.1;
            $cost *= 0.8;
        }

        else if($turnType == 'trust' || $turnType == 'pop'){
            $score *= 0.9;
            $cost *= 1.2;
        } 
        
        return [$score, $cost, $successRate, $failRate];
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'gold'){
            return $amount * 1.1;
        }

        return $amount;
    }
}