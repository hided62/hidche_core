<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class event_전투특기_척사 extends \sammo\BaseItem{

    protected $rawName = '비급';
    protected $name = '비급(척사)';
    protected $info = '[전투] 지역·도시 병종 상대로 대미지 +20%, 아군 피해 -20%';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 3000;

    public function getWarPowerMultiplier(WarUnit $unit):array{
        $opposeCrewType = $unit->getOppose()->getCrewType();
        if($opposeCrewType->reqCities || $opposeCrewType->reqRegions){
            return [1.2, 0.8];
        }
        return [1, 1];
    }
}