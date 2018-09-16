<?php
namespace sammo\TriggerSpecialWar;
use \sammo\iActionTrigger;
use \sammo\General;
use \sammo\SpecialityConst;

class che_척사 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 75;
    static $name = '척사';
    static $info = '[전투] 지역·도시 병종 상대로 대미지 +10%, 아군 피해 -10%';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_POWER,
        SpecialityConst::STAT_INTEL
    ];
}