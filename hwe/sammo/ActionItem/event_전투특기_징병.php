<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class event_전투특기_징병 extends \sammo\BaseItem{

    protected $rawName = '비급';
    protected $name = '비급(징병)';
    protected $info = '[군사] 징병/모병 시 훈사 70/84 제공<br>[기타] 통솔 순수 능력치 보정 +25%, 징병/모병/소집해제 시 인구 변동 없음';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 3000;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost') return $value * 0.5;
            if($varType == 'train' || $varType == 'atmos'){
                if($turnType === '징병'){
                    return 70;
                }
                else{
                    return 84;
                }
            }
        }

        if($turnType == '징집인구' && $varType == 'score'){
            return 0;
        }

        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'leadership'){
            return $value + $general->getVar('leadership') * 0.25;
        }
        return $value;
    }
}