<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;

class che_귀병 implements iAction{
    use \sammo\DefaultAction;

    static $id = 40;
    static $name = '귀병';
    static $info = '[군사] 귀병 계통 징·모병비 -10%<br>[전투] 계략 성공 확률 +20%p';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_INTEL | SpecialityConst::ARMY_WIZARD | SpecialityConst::REQ_DEXTERITY
    ];
}