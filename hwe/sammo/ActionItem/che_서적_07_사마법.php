<?php
namespace sammo\ActionItem;

use sammo\BaseWarUnitTrigger;
use \sammo\iAction;
use \sammo\General;
use sammo\WarUnit;
use sammo\WarUnitTrigger\che_반계발동;
use sammo\WarUnitTrigger\che_반계시도;
use sammo\WarUnitTriggerCaller;

class che_서적_07_사마법 extends \sammo\BaseStatItem{
    protected $cost = 200;
    protected $buyable = false;

    public function __construct()
    {
        parent::__construct();
        $this->info .= "<br>[전투] 상대의 계략을 10% 확률로 되돌림";
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_반계시도($unit, BaseWarUnitTrigger::TYPE_ITEM+BaseWarUnitTrigger::TYPE_DEDUP_TYPE_BASE*207, 0.1),
            new che_반계발동($unit)
        );
    }
}