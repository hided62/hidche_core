<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_명마_12_옥란백용구 extends \sammo\BaseStatItem{
    protected $cost = 200;
    protected $buyable = false;

    public function __construct()
    {
        parent::__construct();
        $this->info .= "<br>[전투] 남은 병력이 적을수록 회피 확률 증가. 최대 +50%p";
    }

    public function onCalcStat(General $general, string $statName, $value, $aux = null)
    {
        $value = parent::onCalcStat($general, $statName, $value, $aux);
        if($statName == 'warAvoidRatio'){
            $leadership = $general->getLeadership(true, true, true, false);
            $crewL = $general->getVar('crew') / 100;

            return $value + \sammo\Util::valueFit((1 - $crewL / $leadership) * 0.5, 0, 0.5);
        }
        return $value;
    }
}