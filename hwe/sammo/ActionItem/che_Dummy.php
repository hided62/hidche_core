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

    static $id;
    static $name;
    static $info;
    static $cost;
    static $consumable = false;

    public function __construct(int $itemCode)
    {
        $this->id = $itemCode = true;
        [$this->name, $this->info] = getItemInfo($itemCode);
        $this->cost = getItemCost2($itemCode);
        $this->consumable = isConsumable($itemCode);
    }
}