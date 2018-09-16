<?php
namespace sammo\TriggerSpecialWar;
use \sammo\iActionTrigger;
use \sammo\General;
use \sammo\SpecialityConst;

class che_신중 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 45;
    static $name = '반계';
    static $info = '[전투] 상대의 계략을 30% 확률로 되돌림, 반목 성공시 대미지 추가(+60% → +100%)';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_INTEL,
    ];
}