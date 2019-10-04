<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;
use \sammo\WarUnit;

class che_척사 implements iAction{
    use \sammo\DefaultAction;

    static $id = 75;
    static $name = '척사';
    static $info = '[전투] 지역·도시 병종 상대로 대미지 +10%, 아군 피해 -10%';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_STRENGTH,
        SpecialityConst::STAT_INTEL
    ];

    public function getWarPowerMultiplier(WarUnit $unit):array{
        $opposeCrewType = $unit->getOppose()->getCrewType();
        if($opposeCrewType->reqCities || $opposeCrewType->reqRegions){
            return [1.1, 0.9];
        }
        return [1, 1];
    }
}