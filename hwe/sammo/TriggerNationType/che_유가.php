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

    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array{
        if($turnType == 'agri' || $turnType == 'comm'){
            $score *= 1.1;
            $cost *= 0.8;
        }
        
        return [$score, $cost, $successRate, $failRate];
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'rice'){
            return $amount * 0.9;
        }
        
        return $amount;
    }
}