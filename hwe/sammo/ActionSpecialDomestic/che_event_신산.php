<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class che_event_신산 extends \sammo\BaseSpecial{

    protected $id = 41;
    protected $name = '신산';
    protected $info = '[계략] 화계·탈취·파괴·선동 : 성공률 +10%p<br>[전투] 계략 시도 확률 +20%p, 계략 성공 확률 +20%p';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_INTEL,
    ];

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '계략'){
            if($varType == 'success') return $value + 0.1;
        }
        
        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicTrialProb'){
            return $value + 0.2;
        }
        if($statName === 'warMagicSuccessProb'){
            return $value + 0.2;
        }
        return $value;
    }
}