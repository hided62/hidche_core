<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use sammo\WarUnit;

class che_명마_07_백상 extends \sammo\BaseStatItem{
    protected $cost = 200;
    protected $buyable = false;

    public function __construct()
    {
        parent::__construct();
        $this->info .= "<br>[전투] 공격력 +20%, 소모 군량 +10%, 공격 시 페이즈 -1";
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        $value = parent::onCalcStat($general, $statName, $value, $aux);
        if($statName == 'killRice'){
            return $value * 1.1;
        }
        if($statName === 'initWarPhase'){
            return $value - 1;
        }
        return $value;
    }

    public function getWarPowerMultiplier(WarUnit $unit): array
    {
        return [1.2, 1];
    }
}