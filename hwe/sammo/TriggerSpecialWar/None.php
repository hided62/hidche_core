<?php
namespace sammo\TriggerSpecialWar;
use \sammo\iActionTrigger;
use \sammo\General;
use \sammo\SpecialityConst;

class None implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 0;
    static $name = '-';
    static $info = null;

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 0;
    static $type = [
        SpecialityConst::DISABLED
    ];

}