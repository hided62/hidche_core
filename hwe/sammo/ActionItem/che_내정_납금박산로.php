<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use sammo\Util;

class che_내정_납금박산로 extends \sammo\BaseItem{

    protected $rawName = '납금박산로';
    protected $name = '납금박산로(내정)';
    protected $info = '[내정] 내정 성공률 +20%p';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStrategic(string $turnType, string $varType, $value){
        if(in_array($turnType, ['상업', '농업', '기술', '성벽', '수비', '치안', '민심', '인구'])){
            if($varType == 'success') return $value + 0.2;
        }
        return $value;
    }
}