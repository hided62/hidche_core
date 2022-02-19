<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_계략_육도 extends \sammo\BaseItem{

    protected $rawName = '육도';
    protected $name = '육도(계략)';
    protected $info = '[계략] 화계·탈취·파괴·선동 : 성공률 +20%p<br>[전투] 계략 시도 확률 +10%p, 계략 성공 확률 +10%p';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '계략'){
            if($varType == 'success') return $value + 0.2;
        }

        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicTrialProb'){
            return $value + 0.1;
        }
        if($statName === 'warMagicSuccessProb'){
            return $value + 0.1;
        }
        return $value;
    }
}