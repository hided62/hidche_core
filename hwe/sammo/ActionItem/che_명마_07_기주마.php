<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_명마_07_기주마 extends \sammo\BaseStatItem{
    protected $cost = 200;
    protected $buyable = false;

    public function __construct()
    {
        parent::__construct();
        $this->info .= "<br>[전투] 공격 시 페이즈 +1";
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        $value = parent::onCalcStat($general, $statName, $value, $aux);
        if($statName === 'initWarPhase'){
            return $value + 1;
        }
        return $value;
    }
}