<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_음양가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '음양가';
    static $info = '';
    static $pros = '내정↑ 인구↑';
    static $cons = '기술↓ 전략↓';

    public function onCalcDomesticTurnScore(string $turnType, float $score, float $cost, float $successRate, float $failRate):array{
        if($turnType == 'agri' || $turnType == 'comm'){
            $score *= 1.1;
            $cost *= 0.8;
        }
        else if($turnType == 'tech'){
            $score *= 0.9;
            $cost *= 1.2;
        }
        
        return [$score, $cost, $successRate, $failRate];
    }

    public function onCalcStragicDelay(array $nation, int $commandType, int $turn):int{
        return $turn * 2;
    }
}