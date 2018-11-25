<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;

class che_거상 implements iAction{
    use \sammo\DefaultAction;

    static $id = 999;
    static $name = '거상';
    static $info = '<비활성화>';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 0;
    static $type = [];

}