<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_계략_이추 extends \sammo\BaseItem{

    protected static $id = 5;
    protected static $name = '이추(계략)';
    protected static $info = '[계략] 화계·탈취·파괴·선동 : 성공률 +10%p';
    protected static $cost = 1000;
    protected static $consumable = true;
    protected static $buyable = true;
    protected static $reqSecu = 1000;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '계략'){
            if($varType == 'success') return $value + 0.1;
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