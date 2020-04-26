<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;

class None extends \sammo\BaseSpecial{

    protected $id = 0;
    protected $name = '-';
    protected $info = '';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 0;
    static $type = [
        SpecialityHelper::DISABLED
    ];

}