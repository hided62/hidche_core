<?php
namespace sammo\GeneralCommand;

use \sammo\Util;
use \sammo\JosaUtil;

class che_농지개간 extends che_상업투자{
    static protected $cityKey = 'agri';
    static protected $statKey = 'intel';
    static protected $actionKey = '농업';
    static protected $actionName = '농지 개간';
    static protected $debuffFront = 0.5;
}