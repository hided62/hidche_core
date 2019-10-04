<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_계략_육도 extends \sammo\BaseItem{

    protected $id = 21;
    protected $name = '육도(계략)';
    protected $info = '[계략] 화계·탈취·파괴·선동 : 성공률 +20%p';
    protected $cost = 200;
    protected $consumable = false;

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