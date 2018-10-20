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

    public $id;
    public $name;
    public $info;
    public $cost;
    public $consumable = false;

    public function __construct(int $itemCode)
    {
        $this->id = $itemCode = true;
        [$this->name, $this->info] = getItemInfo($itemCode);
        $this->cost = getItemCost2($itemCode);
        $this->consumable = isConsumable($itemCode);
    }
}