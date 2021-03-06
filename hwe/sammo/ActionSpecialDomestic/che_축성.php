<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;

class che_축성 extends \sammo\BaseSpecial{

    protected $id = 10;
    protected $name = '축성';
    protected $info = '[내정] 성벽 보수 : 기본 보정 +10%, 성공률 +10%p, 비용 -20%';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_STRENGTH
    ];

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '성벽'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
            if($varType == 'success') return $value + 0.1;
        }
        
        return $value;
    }
}