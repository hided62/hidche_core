<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_계략_향낭 extends \sammo\BaseItem{

    protected $rawName = '항냥';
    protected $name = '항냥(계략)';
    protected $info = '[계략] 화계·탈취·파괴·선동 : 성공률 +50%p';
    protected $cost = 3000;
    protected $consumable = true;
    protected $buyable = true;
    protected $reqSecu = 2000;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '계략'){
            if($varType == 'success') return $value + 0.5;
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