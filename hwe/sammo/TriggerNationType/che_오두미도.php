<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_오두미도 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '오두미도';
    static $info = '';
    static $pros = '쌀수입↑ 인구↑';
    static $cons = '기술↓ 수성↓ 내정↓';


    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array{
        if($turnType == 'agri' || $turnType == 'comm'){
            $score *= 0.9;
            $cost *= 1.2;
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
        if($type == 'rice'){
            return $amount * 1.1;
        }
        
        return $amount;
    }

}