<?php
namespace sammo;
use \sammo\iAction;
use \sammo\General;

//XXX: 아직 아이템 구현이 끝나지 않았으므로 바뀔 수 있음
class BaseItem implements iAction{
    use \sammo\DefaultAction;

    protected static $id = 0;
    protected static $name = '-';
    protected static $info = '';
    protected static $cost = null;
    protected static $consumable = false;

    function getID(){
        return $this->id;
    }
    function getName(){
        return $this->name;
    }
    function getInfo(){
        return $this->info;
    }
    function getCost(){
        return $this->cost;
    }
    function isConsumable(){
        return $this->consumable;
    }

    function isValidTurnItem(string $actionType, string $command):bool{
        return false;
    }
}