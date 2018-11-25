<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst, GameUnitConst,
    LastTurn,
    Command
};


class che_모병 extends che_징병{
    static protected $actionName = '모병';
    static protected $costOffset = 2;

    protected static function initStatic()
    {
        static::$defaultTrain = GameConst::$defaultTrainHigh;
        static::$defaultAtmos = GameConst::$defaultAtmosHigh;
    }

}