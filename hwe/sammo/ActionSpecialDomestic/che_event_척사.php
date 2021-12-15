<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class che_event_척사 extends \sammo\BaseSpecial{

    protected $id = 75;
    protected $name = '척사';
    protected $info = '[전투] 지역·도시 병종 상대로 대미지 +20%, 아군 피해 -20%';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_LEADERSHIP,
        SpecialityHelper::STAT_STRENGTH,
        SpecialityHelper::STAT_INTEL
    ];

    public function getWarPowerMultiplier(WarUnit $unit):array{
        $opposeCrewType = $unit->getOppose()->getCrewType();
        if($opposeCrewType->reqCities || $opposeCrewType->reqRegions){
            return [1.2, 0.8];
        }
        return [1, 1];
    }
}