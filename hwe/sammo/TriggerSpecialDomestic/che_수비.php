<?php
namespace sammo\TriggerSpecialDomestic;
use \sammo\iActionTrigger;
use \sammo\General;
use \sammo\SpecialityConst;

class che_수비 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 11;
    static $name = '수비';
    static $info = '[내정] 수비 강화 : 기본 보정 +10%, 성공률 +10%p, 비용 -20%';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_POWER
    ];

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if($turnType == 'def'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
            if($varType == 'succ') return $value + 0.1;
        }
        
        return $value;
    }
}