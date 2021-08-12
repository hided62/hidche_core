<?php

namespace sammo\Command\General;

use \sammo\DB;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\General;
use \sammo\ActionLogger;
use \sammo\GameConst;
use \sammo\GameUnitConst;
use \sammo\LastTurn;
use \sammo\Command;

class che_모병 extends che_징병
{
    static protected $actionName = '모병';
    static protected $costOffset = 2;

    static protected $defaultTrain;
    static protected $defaultAtmos;

    static protected $isInitStatic = false;
    protected static function initStatic()
    {
        static::$defaultTrain = GameConst::$defaultTrainHigh;
        static::$defaultAtmos = GameConst::$defaultAtmosHigh;
    }

    public function getCommandDetailTitle(): string
    {
        return "{$this->getName()}(통솔경험, 자금×2)";
    }
}
