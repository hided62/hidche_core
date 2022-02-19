<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\GameUnitConst;
use \sammo\WarUnit;
use \sammo\WarUnitCity;

class event_전투특기_공성 extends \sammo\BaseItem{

    protected $rawName = '비급';
    protected $name = '비급(공성)';
    protected $info = '[군사] 차병 계통 징·모병비 -10%<br>[전투] 성벽 공격 시 대미지 +100%,<br>공격시 상대 병종에/수비시 자신 병종 숙련에 차병 숙련을 가산';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 3000;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost' && $aux['armType'] == GameUnitConst::T_SIEGE) return $value * 0.9;
        }

        return $value;
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        if($unit->getOppose() instanceof WarUnitCity){
            return [2, 1];
        }
        return [1, 1];
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if(\sammo\Util::starts_with($statName, 'dex')){
            $myArmType = 'dex'.GameUnitConst::T_SIEGE;
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