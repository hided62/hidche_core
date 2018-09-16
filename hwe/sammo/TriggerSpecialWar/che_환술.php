<?php
namespace sammo\TriggerSpecialWar;
use \sammo\iActionTrigger;
use \sammo\General;
use \sammo\SpecialityConst;

class che_환술 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 42;
    static $name = '환술';
    static $info = '[전투] 계략 성공 확률 +10%p, 계략 성공 시 대미지 +30%';

    static $selectWeightType = SpecialityConst::WEIGHT_PERCENT;
    static $selectWeight = 5;
    static $type = [
        SpecialityConst::STAT_INTEL,
    ];
}