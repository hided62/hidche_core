<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\GameUnitConst;
use \sammo\WarUnit;

class event_전투특기_귀병 extends \sammo\BaseItem{

    protected $rawName = '비급';
    protected $name = '비급(귀병)';
    protected $info = '[군사] 귀병 계통 징·모병비 -10%<br>[전투] 계략 성공 확률 +20%p,<br>공격시 상대 병종에/수비시 자신 병종 숙련에 귀병 숙련을 가산';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 3000;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost' && $aux['armType'] == GameUnitConst::T_WIZARD) return $value * 0.9;
        }

        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicSuccessProb'){
            return $value + 0.2;
        }
        if(\sammo\Util::starts_with($statName, 'dex')){
            $myArmType = 'dex'.GameUnitConst::T_WIZARD;
            $opposeArmType = 'dex'.$aux['opposeType']->armType;;
            if($aux['isAttacker'] && $opposeArmType === $statName){
                return $value + $general->getVar($myArmType);
            }
            if(!$aux['isAttacker'] && $myArmType === $statName){
                return $value + $general->getVar($myArmType);
            }
        }
        return $value;
    }
}