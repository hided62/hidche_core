<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\GameUnitConst;
use \sammo\WarUnit;

class che_event_보병 extends \sammo\BaseSpecial{

    protected $id = 50;
    protected $name = '보병';
    protected $info = '[군사] 보병 계통 징·모병비 -10%<br>[전투] 공격 시 아군 피해 -10%, 수비 시 아군 피해 -20%,<br>공격시 상대 병종에/수비시 자신 병종 숙련에 보병 숙련을 가산';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_LEADERSHIP | SpecialityHelper::REQ_DEXTERITY | SpecialityHelper::ARMY_FOOTMAN | SpecialityHelper::STAT_NOT_INTEL,
        SpecialityHelper::STAT_STRENGTH | SpecialityHelper::REQ_DEXTERITY | SpecialityHelper::ARMY_FOOTMAN
    ];

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost' && $aux['armType'] == GameUnitConst::T_FOOTMAN) return $value * 0.9;
        }
        
        return $value;
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        if($unit->isAttacker()){
            return [1, 0.9];
        }
        return [1, 0.8];
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if(\sammo\Util::starts_with($statName, 'dex')){
            $myArmType = 'dex'.GameUnitConst::T_FOOTMAN;
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