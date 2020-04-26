<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\GameUnitConst;
use \sammo\WarUnit;
use \sammo\WarUnitCity;

class che_공성 extends \sammo\BaseSpecial{

    protected $id = 53;
    protected $name = '공성';
    protected $info = '[군사] 차병 계통 징·모병비 -10%<br>[전투] 성벽 공격 시 대미지 +100%';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_LEADERSHIP | SpecialityHelper::REQ_DEXTERITY | SpecialityHelper::ARMY_SIEGE,
    ];

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
}