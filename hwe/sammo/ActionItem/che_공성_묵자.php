<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\GameUnitConst;
use \sammo\WarUnit;
use \sammo\WarUnitCity;

class che_공성_묵자 extends \sammo\BaseItem{

    protected $rawName = '묵자';
    protected $name = '묵자(공성)';
    protected $info = '[전투] 성벽 공격 시 대미지 +50%';
    protected $cost = 200;
    protected $consumable = false;

    public function getWarPowerMultiplier(WarUnit $unit):array{
        if($unit->getOppose() instanceof WarUnitCity){
            return [1.5, 1];
        }
        return [1, 1];
    }
}