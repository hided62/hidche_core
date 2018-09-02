<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;

class che_유가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 2;
    static $name = '유가';
    static $info = '';
    static $pros = '내정↑ 민심↑';
    static $cons = '쌀수입↓';

    public function onCalcDomesticTurnScore(General $general, string $turnType, float $score, float $cost, float $successRate, float $failRate){
        if($turnType == 'agri' || $turnType == 'comm'){
            $score *= 1.1;
            $cost *= 0.8;
        }
        
        return [$score, $cost, $successRate, $failRate];
    }
}