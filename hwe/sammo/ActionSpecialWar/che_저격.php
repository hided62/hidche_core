<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;
use \sammo\WarUnit;

class che_저격 implements iAction{
    use \sammo\DefaultAction;

    static $id = 70;
    static $name = '저격';
    static $info = '[전투] 전투 개시 시 1/3 확률로 저격 발동';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_POWER,
        SpecialityConst::STAT_INTEL
    ];
}