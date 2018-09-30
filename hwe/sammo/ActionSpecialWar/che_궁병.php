<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;

class che_궁병 implements iAction{
    use \sammo\DefaultAction;

    static $id = 51;
    static $name = '궁병';
    static $info = '[군사] 궁병 계통 징·모병비 -10%<br>[전투] 회피 확률 +20%p';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP | SpecialityConst::REQ_DEXTERITY | SpecialityConst::ARMY_ARCHER,
        SpecialityConst::STAT_POWER | SpecialityConst::REQ_DEXTERITY | SpecialityConst::ARMY_ARCHER
    ];
}