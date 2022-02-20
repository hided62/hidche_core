<?php

namespace sammo\ActionItem;

use \sammo\iAction;
use \sammo\General;
use sammo\Util;
use sammo\WarUnit;
use sammo\WarUnitTrigger\전투력보정;
use sammo\WarUnitTriggerCaller;

class che_불굴_상편 extends \sammo\BaseItem
{

    protected $rawName = '상편';
    protected $name = '상편(불굴)';
    protected $info = '[전투] 남은 병력이 적을수록 공격력 증가. 최대 +50%';
    protected $cost = 200;
    protected $consumable = false;

    public function getBattlePhaseSkillTriggerList(WarUnit $unit): ?WarUnitTriggerCaller
    {
        $leadership = $unit->getGeneral()->getLeadership();
        $crew = $unit->getGeneral()->getVar('crew');

        $crewRatio = Util::valueFit($crew / ($leadership * 100), 0, 1);
        return new WarUnitTriggerCaller(
            new 전투력보정($unit, 1 + 0.5 * (1 - $crewRatio))
        );
    }
}
