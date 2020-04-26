<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;

class che_귀모 extends \sammo\BaseSpecial{

    protected $id = 31;
    protected $name = '귀모';
    protected $info = '[계략] 화계·탈취·파괴·선동 : 성공률 +20%p';

    static $selectWeightType = SpecialityHelper::WEIGHT_PERCENT;
    static $selectWeight = 2.5;
    static $type = [
        SpecialityHelper::STAT_LEADERSHIP,
        SpecialityHelper::STAT_STRENGTH,
        SpecialityHelper::STAT_INTEL
    ];

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '계략'){
            if($varType == 'success') return $value + 0.2;
        }
        
        
        return $value;
    }
}