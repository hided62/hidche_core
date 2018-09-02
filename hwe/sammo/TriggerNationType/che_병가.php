<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_병가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '병가';
    static $info = '';
    static $pros = '기술↑ 수성↑';
    static $cons = '인구↓ 민심↓';

    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array{
        if($turnType == 'tech'){
            $score *= 1.1;
            $cost *= 0.8;
        }
        
        return [$score, $cost, $successRate, $failRate];
    }
}