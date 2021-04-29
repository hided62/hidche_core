<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_서적_01_효경전 extends \sammo\BaseStatItem{
    protected $cost = 1000;
    protected $buyable = true;
    protected $reqSecu = 1000;

    public function __construct()
    {
        parent::__construct();
        $this->info .= "<br>[전투] 계략 시도 확률 +1%p";
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicTrialProb'){
            return $value + 0.01;
        }
        return $value;
    }
}