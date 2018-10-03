<?php
namespace sammo\GeneralCommand;

use \sammo\Util;
use \sammo\JosaUtil;

class che_수비강화 extends che_상업투자{
    static protected $cityKey = 'def';
    static protected $statKey = 'power';
    static protected $actionKey = '수비';
    static protected $actionName = '수비 강화';
    static protected $debuffFront = 0.5;
}