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

    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array{
        if($turnType == 'def' || $turnType == 'wall'){
            $score *= 1.1;
            $cost *= 0.8;
        }
        
        else if($turnType == 'agri' || $turnType == 'comm'){
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

    public function onCalcStragicDelay(array $nation, int $commandType, int $turn):int{
        return Util::round($turn / 2);
    }
}