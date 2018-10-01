<?php
namespace sammo\GeneralCommand;

use \sammo\Util;
use \sammo\JosaUtil;

class che_성벽보수 extends che_상업투자{
    static $cityKey = 'wall';
    static $statKey = 'power';
    static $actionKey = '성벽';
    static $actionName = '성벽 보수';
    static $debuffFront = 0.25;
}