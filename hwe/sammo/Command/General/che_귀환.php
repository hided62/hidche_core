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


use function \sammo\{
    tryUniqueItemLottery
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;



class che_귀환 extends Command\GeneralCommand{
    static protected $actionName = '귀환';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        [$reqGold, $reqRice] = $this->getCost();
        
        $this->runnableConstraints=[
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::NotWanderingNation(),
            ConstraintHelper::NotCapital(true),
        ];
    }

    public function getCost():array{
        return [0, 0];
    }
    
    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $generalID = $general->getID();
        $date = substr($general->getVar('turntime'),11,5);

        switch($general->getVar('level')){
            case 2: [$cityID, $cityName] = $db->queryFirstList('SELECT city, name FROM city WHERE gen3=%i', $generalID); break;
            case 3: [$cityID, $cityName] = $db->queryFirstList('SELECT city, name FROM city WHERE gen2=%i', $generalID); break;
            case 4: [$cityID, $cityName] = $db->queryFirstList('SELECT city, name FROM city WHERE gen1=%i', $generalID); break;
            default: [$cityID, $cityName] = $db->queryFirstList('SELECT city, name FROM city WHERE city=%i', $this->nation['capital']); break;
        }

        $josaRo = JosaUtil::pick($cityName, '로');

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("<G><b>{$cityName}</b></>{$josaRo} 귀환했습니다. <1>$date</>");

        $exp = 70;
        $ded = 100;
        $exp = $general->onCalcStat($general, 'experience', $exp);
        $ded = $general->onCalcStat($general, 'dedication', $ded);
        
        $general->setVar('city', $cityID);

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar('leader2', 1);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;
    }

    
}