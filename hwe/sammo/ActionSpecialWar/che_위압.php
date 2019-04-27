<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;
use \sammo\WarUnit;

class che_위압 implements iAction{
    use \sammo\DefaultAction;

    static $id = 63;
    static $name = '위압';
    static $info = '[전투] 훈련/사기≥90, 병력≥1,000 일 때 첫 페이즈 위압 발동(적 공격 불가)';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_POWER
    ];
}