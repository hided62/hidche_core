<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;

class che_경작 implements iAction{
    use \sammo\DefaultAction;

    protected $id = 1;
    protected $name = '경작';
    protected $info = '[내정] 농지 개간 : 기본 보정 +10%, 성공률 +10%p, 비용 -20%';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_INTEL
    ];

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '농업'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
            if($varType == 'success') return $value + 0.1;
        }
        
        return $value;
    }
}