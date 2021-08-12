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

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;



class che_내정특기초기화 extends che_전투특기초기화{
    static protected $actionName = '내정 특기 초기화';
    static protected $specialType = 'special';
    static protected $speicalAge = 'specage';
    static protected $specialText = '내정 특기';

}