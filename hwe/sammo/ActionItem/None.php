<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class None extends \sammo\BaseItem{

    protected $name = '-';
    protected $info = null;
    protected $cost = 0;
    protected $consumable = false;
    protected $buyable = true;
}