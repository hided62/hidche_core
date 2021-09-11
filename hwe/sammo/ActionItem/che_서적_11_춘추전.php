<?php
namespace sammo\ActionItem;

use sammo\BaseWarUnitTrigger;
use \sammo\iAction;
use \sammo\General;
use sammo\WarUnit;
use sammo\WarUnitTrigger\WarActivateSkills;
use sammo\WarUnitTriggerCaller;

class che_서적_11_춘추전 extends \sammo\BaseStatItem{
    protected $cost = 200;
    protected $buyable = false;

    public function __construct()
    {
        parent::__construct();
        $this->info .= "<br>[전투] 상대의 계략 성공 확률 -10%p";
    }

    public function onCalcOpposeStat(General $general, string $statName, $value, $aux = null)
    {
        $debuff = [
            'warMagicSuccessProb' => 0.1,
        ][$statName] ?? 0;
        return $value - $debuff;
    }
}