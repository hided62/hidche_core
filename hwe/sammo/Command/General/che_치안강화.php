<?php
namespace sammo\GeneralCommand;

use \sammo\Util;
use \sammo\JosaUtil;

class che_치안강화 extends che_상업투자{
    static protected $cityKey = 'secu';
    static protected $statKey = 'power';
    static protected $actionKey = '치안';
    static protected $actionName = '치안 강화';
    static protected $debuffFront = 1;
}