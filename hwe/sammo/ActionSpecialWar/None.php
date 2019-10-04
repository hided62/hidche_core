<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;

class None implements iAction{
    use \sammo\DefaultAction;

    protected $id = 0;
    protected $name = '-';
    protected $info = null;

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 0;
    static $type = [
        SpecialityConst::DISABLED
    ];

}