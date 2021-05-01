<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_서적_07_위료자 extends \sammo\BaseStatItem{
    protected $cost = 200;
    protected $buyable = false;

    public function __construct()
    {
        parent::__construct();
        $this->info .= "<br>[전투] 계략 시도 확률 +20%p";
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        $value = parent::onCalcStat($general, $statName, $value, $aux);
        if($statName === 'warMagicTrialProb'){
            return $value + 0.2;
        }
        return $value;
    }
}