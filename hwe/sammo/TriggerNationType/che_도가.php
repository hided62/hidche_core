<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_도가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '도가';
    static $info = '';
    static $pros = '인구↑';
    static $cons = '기술↓ 치안↓';

    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array{
        if($turnType == 'tech'){
            $score *= 0.9;
            $cost *= 1.2;
        }
        else if($turnType == 'secu'){
            $score *= 0.9;
            $cost *= 1.2;
        }
        
        return [$score, $cost, $successRate, $failRate];
    }
}