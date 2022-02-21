<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use sammo\WarUnit;

class che_농성_위공자병법 extends \sammo\BaseItem{

    protected $rawName = '위공자병법';
    protected $name = '위공자병법(농성)';
    protected $info = '[계략] 장수 주둔 도시 화계·탈취·파괴·선동 : 성공률 -30%p<br>[전투] 상대 계략 시도 확률 -10%p, 상대 계략 성공 확률 -10%p';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'sabotageDefence'){
            return $value + 0.3;
        }
        return $value;
    }

    public function onCalcOpposeStat(General $general, string $statName, $value, $aux = null)
    {
        if($statName === 'warMagicTrialProb'){
            return $value - 0.1;
        }
        if($statName === 'warMagicSuccessProb'){
            return $value - 0.1;
        }
        return $value;
    }
}
