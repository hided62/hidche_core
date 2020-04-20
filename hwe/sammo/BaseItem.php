<?php
namespace sammo;
use \sammo\iAction;
use \sammo\General;

//XXX: 아직 아이템 구현이 끝나지 않았으므로 바뀔 수 있음
class BaseItem implements iAction{
    use \sammo\DefaultAction;

    protected $id = 0;
    protected $rawName = '-';
    protected $name = '-';
    protected $info = '';
    protected $cost = null;
    protected $consumable = false;
    protected $buyable = false;
    protected $reqSecu = 0;

    function getID(){
        return $this->id;
    }

    function getRawName(){
        return $this->rawName;
    }

    public function getRawClassName(bool $shortName=true):string{
        if($shortName){
            return Util::getClassNameFromObj($this);
        }
        return static::class;
    }
    
    function getCost(){
        return $this->cost;
    }
    function isConsumable(){
        return $this->consumable;
    }

    function isBuyable(){
        return $this->buyable;
    }

    function getReqSecu(){
        return $this->reqSecu;
    }

    function isConsumableNow(string $actionType, string $command):bool{
        return false;
    }
}