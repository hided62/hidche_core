<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class che_event_신중 extends \sammo\BaseSpecial{

    protected $id = 44;
    protected $name = '신중';
    protected $info = '[전투] 계략 성공 확률 100%';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_INTEL,
    ];

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicSuccessProb'){
            return $value + 1;
        }
        return $value;
    }
}