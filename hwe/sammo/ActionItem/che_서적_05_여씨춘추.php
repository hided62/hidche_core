<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_서적_05_여씨춘추 extends \sammo\BaseStatItem{
    protected $cost = 15000;
    protected $buyable = true;
    protected $reqSecu = 5000;

    public function __construct()
    {
        parent::__construct();
        $this->info .= "<br>[전투] 계략 시도 확률 +3%p";
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicTrialProb'){
            return $value + 0.03;
        }
        return $value;
    }
}