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
        if($turnType == 'agri' || $turnType == 'comm'){
            $score *= 0.9;
            $cost *= 1.2;
        }
        
        return [$score, $cost, $successRate, $failRate];
    }
}