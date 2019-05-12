<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\GeneralTrigger;
use function sammo\isConsumable;
use function sammo\getItemInfo;
use function sammo\getItemCost2;

//XXX:임시용!
class che_Dummy extends \sammo\BaseItem{

    protected $_id;
    protected $_name;
    protected $_info;
    protected $_cost;
    protected $_consumable = false;

    function getID(){
        return $this->_id;
    }
    function getName(){
        return $this->_name;
    }
    function getInfo(){
        return $this->_info;
    }
    function getCost(){
        return $this->_cost;
    }
    function isConsumable(){
        return $this->_consumable;
    }

    public function __construct(int $itemCode)
    {
        $this->_id = $itemCode;
        [$this->_name, $this->_info] = getItemInfo($itemCode);
        $this->_cost = getItemCost2($itemCode);
        $this->_consumable = isConsumable($itemCode);
    }
}