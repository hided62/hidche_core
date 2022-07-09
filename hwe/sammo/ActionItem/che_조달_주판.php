<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use sammo\Util;

class che_조달_주판 extends \sammo\BaseItem{

    protected $rawName = '주판';
    protected $name = '주판(조달)';
    protected $info = '[내정] 물자조달 성공 확률 +20%p, 물자조달 획득량 +100%p';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null): float{
        if($turnType === '조달'){
            if($varType == 'success') return $value + 0.2;
            if($varType == 'score') return $value * 2;
        }
        return $value;
    }
}