<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_서적_08_전론 extends \sammo\BaseStatItem{
    protected $cost = 200;
    protected $buyable = false;

    public function __construct()
    {
        parent::__construct();
        $this->info .= "<br>[전투] 계략 성공 시 대미지 +20%";
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        $value = parent::onCalcStat($general, $statName, $value, $aux);
        if($statName === 'warMagicSuccessDamage'){
            return $value * 1.2;
        }
        return $value;
    }
}