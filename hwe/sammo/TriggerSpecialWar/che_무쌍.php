<?php
namespace sammo\TriggerSpecialWar;
use \sammo\iActionTrigger;
use \sammo\General;
use \sammo\SpecialityConst;

class che_무쌍 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 61;
    static $name = '무쌍';
    static $info = '[전투] 대미지 +10%, 공격 시 필살 확률 +10%p';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_POWER
    ];
}