<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;

class che_거상 extends \sammo\BaseSpecial{

    protected $id = 999;
    protected $name = '거상';
    protected $info = '<비활성화>';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 0;
    static $type = [];

}