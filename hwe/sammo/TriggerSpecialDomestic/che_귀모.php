<?php
namespace sammo\TriggerSpecialDomestic;
use \sammo\iActionTrigger;
use \sammo\General;
use \sammo\SpecialityConst;

class che_귀모 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 31;
    static $name = '귀모';
    static $info = '[계략] 화계·탈취·파괴·선동 : 성공률 +20%p';

    static $selectWeightType = SpecialityConst::WEIGHT_PERCENT;
    static $selectWeight = 2.5;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_POWER,
        SpecialityConst::STAT_INTEL
    ];

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if($turnType == '계략'){
            if($varType == 'success') return $value + 0.2;
        }
        
        
        return $value;
    }
}