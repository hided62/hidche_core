<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;

class che_공성 implements iAction{
    use \sammo\DefaultAction;

    static $id = 53;
    static $name = '공성';
    static $info = '[군사] 차병 계통 징·모병비 -10%<br>[전투] 성벽 공격 시 대미지 +100%';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP | SpecialityConst::REQ_DEXTERITY | SpecialityConst::ARMY_SIEGE,
        SpecialityConst::STAT_POWER | SpecialityConst::REQ_DEXTERITY | SpecialityConst::ARMY_SIEGE,
        SpecialityConst::STAT_INTEL | SpecialityConst::REQ_DEXTERITY | SpecialityConst::ARMY_SIEGE,
    ];
}