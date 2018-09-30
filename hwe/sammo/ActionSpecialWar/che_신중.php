<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;

class che_신중 implements iAction{
    use \sammo\DefaultAction;

    static $id = 44;
    static $name = '신중';
    static $info = '[전투] 계략 성공 확률 100%';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_INTEL,
    ];
}