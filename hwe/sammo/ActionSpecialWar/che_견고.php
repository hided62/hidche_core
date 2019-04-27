<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;
use \sammo\WarUnit;

class che_견고 implements iAction{
    use \sammo\DefaultAction;

    static $id = 62;
    static $name = '견고';
    static $info = '[전투] 상대 필살 불가, 상대 계략 시도시 성공 확률 -10%p';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_POWER
    ];
}