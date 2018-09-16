<?php
namespace sammo\TriggerSpecialWar;
use \sammo\iActionTrigger;
use \sammo\General;
use \sammo\SpecialityConst;

class che_징병 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 72;
    static $name = '징병';
    static $info = '[군사] 징·모병비 -50%, 통솔 순수 능력치 보정 +15%';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_POWER,
        SpecialityConst::STAT_INTEL
    ];
}