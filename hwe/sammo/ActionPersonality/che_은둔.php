<?php
namespace sammo\ActionPersonality;
use \sammo\iAction;
use \sammo\General;

class che_은둔 implements iAction{
    use \sammo\DefaultAction;

    protected $id = 10;
    protected $name = '은둔';
    protected $info = '명성 -10%, 계급 -10%, 사기 -5, 훈련 -5, 단련 성공률 +10%';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '단련'){
            if($varType == 'success'){
                return $value + 0.1;
            } 
        }

        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName == 'bonusAtmos'){
            return $value - 5;
        }
        if($statName == 'bonusTrain'){
            return $value - 5;
        }
        if($statName == 'experience'){
            return $value * 0.9;
        }
        if($statName == 'dedication'){
            return $value * 0.9;
        }
        return $value;
    }
}