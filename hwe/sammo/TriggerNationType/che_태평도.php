<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_태평도 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '태평도';
    static $info = '';
    static $pros = '인구↑ 민심↑';
    static $cons = '기술↓ 수성↓';

    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array{
        if($turnType == 'trust' || $turnType == 'pop'){
            $score *= 1.1;
            $cost *= 0.8;
        }

        else if($turnType == 'tech'){
            $score *= 0.9;
            $cost *= 1.2;
        }
        else if($turnType == 'def' || $turnType == 'wall'){
            $score *= 0.9;
            $cost *= 1.2;
        }
        
        return [$score, $cost, $successRate, $failRate];
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'pop'){
            return $amount * 1.2;
        }
        
        return $amount;
    }
}