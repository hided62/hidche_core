<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class che_척사_오악진형도 extends \sammo\BaseItem{

    protected $rawName = '오악진형도';
    protected $name = '오악진형도(척사)';
    protected $info = '[전투] 지역·도시 병종 상대로 대미지 +15%, 아군 피해 -15%';
    protected $cost = 200;
    protected $consumable = false;

    public function getWarPowerMultiplier(WarUnit $unit):array{
        $opposeCrewType = $unit->getOppose()->getCrewType();
        if($opposeCrewType->reqCities || $opposeCrewType->reqRegions){
            return [1.15, 0.85];
        }
        return [1, 1];
    }
}