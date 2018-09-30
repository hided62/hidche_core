<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;

class che_격노 implements iAction{
    use \sammo\DefaultAction;

    static $id = 74;
    static $name = '격노';
    static $info = '[전투] 상대방 필살 및 회피 시도시 일정 확률로 격노(필살) 발동, 공격 시 일정 확률로 진노(1페이즈 추가)';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_POWER,
        SpecialityConst::STAT_INTEL
    ];
}