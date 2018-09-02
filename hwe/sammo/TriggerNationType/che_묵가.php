<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_묵가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '묵가';
    static $info = '';
    static $pros = '수성↑';
    static $cons = '기술↓';

    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array{
        if($turnType == 'def' || $turnType == 'wall'){
            $score *= 1.1;
            $cost *= 0.8;
        }

        else if($turnType == 'tech'){
            $score *= 0.9;
            $cost *= 1.2;
        }
        
        return [$score, $cost, $successRate, $failRate];
    }
}