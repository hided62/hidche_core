<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;

class None implements iAction{
    use \sammo\DefaultAction;

    static $id = 0;
    static $name = '-';
    static $info = null;

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 0;
    static $type = [
        SpecialityConst::DISABLED
    ];

}