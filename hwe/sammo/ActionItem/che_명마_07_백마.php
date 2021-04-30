<?php
namespace sammo\ActionItem;

use sammo\BaseWarUnitTrigger;
use \sammo\iAction;
use \sammo\General;
use sammo\WarUnit;
use sammo\WarUnitTrigger\che_퇴각부상무효;
use sammo\WarUnitTriggerCaller;

class che_명마_07_백마 extends \sammo\BaseStatItem{
    protected $cost = 200;
    protected $buyable = false;

    public function __construct()
    {
        parent::__construct();
        $this->info .= "<br>[전투] 전투 종료로 인한 부상 없음";
    }

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_퇴각부상무효($unit, BaseWarUnitTrigger::TYPE_ITEM),
        );
    }
}