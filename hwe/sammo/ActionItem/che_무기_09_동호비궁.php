<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use sammo\WarUnit;
use sammo\WarUnitTrigger\che_저격발동;
use sammo\WarUnitTrigger\che_저격시도;
use sammo\WarUnitTriggerCaller;

class che_무기_09_동호비궁 extends \sammo\BaseStatItem{
    protected $cost = 200;
    protected $buyable = false;

    public function __construct()
    {
        parent::__construct();
        $this->info .= "<br>[전투] 새로운 상대와 전투 시 20% 확률로 저격 발동, 성공 시 사기+10";
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_저격시도($unit, che_저격시도::TYPE_ITEM + che_저격시도::TYPE_DEDUP_TYPE_BASE * 109, 0.15, 20, 40),
            new che_저격발동($unit, che_저격발동::TYPE_ITEM)
        );
    }
}