<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_계략_삼략 extends \sammo\BaseItem{

    protected $id = 22;
    protected $name = '삼략(계략)';
    protected $info = '[계략] 화계·탈취·파괴·선동 : 성공률 +20%p';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '계략'){
            if($varType == 'success') return $value + 0.2;
        }
        
        return $value;
    }

    function isConsumableNow(string $actionType, string $command):bool{
        if($actionType == 'GeneralCommand' && $command == '계략'){
            return true;
        }
        return false;
    }
}