<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class None extends \sammo\BaseItem{

    protected static $id = 0;
    protected static $name = '-';
    protected static $info = null;
    protected static $cost = 0;
    protected static $consumable = false;
    protected static $buyable = true;
}