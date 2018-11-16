<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;

class che_계략_육도 extends \sammo\BaseItem{

    static $id = 21;
    static $name = '육도(계략)';
    static $info = '[계략] 화계·탈취·파괴·선동 : 성공률 +20%p';
    static $cost = 200;
    static $consumable = false;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '계략'){
            if($varType == 'success') return $value + 0.2;
        }
        
        return $value;
    }

    function isValidTurnItem(string $actionType, string $command):bool{
        if($actionType == 'GeneralCommand' && $command == '계략'){
            return true;
        }
        return false;
    }
}