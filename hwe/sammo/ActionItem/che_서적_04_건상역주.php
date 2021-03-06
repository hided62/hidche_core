<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_서적_04_건상역주 extends \sammo\BaseStatItem{
    protected $cost = 10000;
    protected $buyable = true;
    protected $reqSecu = 4000;

    public function __construct()
    {
        parent::__construct();
        $this->info .= "<br>[전투] 계략 시도 확률 +2%p";
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        $value = parent::onCalcStat($general, $statName, $value, $aux);
        if($statName === 'warMagicTrialProb'){
            return $value + 0.02;
        }
        return $value;
    }
}