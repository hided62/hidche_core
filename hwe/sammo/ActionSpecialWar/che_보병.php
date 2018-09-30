<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;

class che_보병 implements iAction{
    use \sammo\DefaultAction;

    static $id = 50;
    static $name = '보병';
    static $info = '[군사] 보병 계통 징·모병비 -10%<br>[전투] 공격 시 아군 피해 -10%, 수비 시 아군 피해 -20%';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP | SpecialityConst::REQ_DEXTERITY | SpecialityConst::ARMY_FOOTMAN,
        SpecialityConst::STAT_POWER | SpecialityConst::REQ_DEXTERITY | SpecialityConst::ARMY_FOOTMAN
    ];
}