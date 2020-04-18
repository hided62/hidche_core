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
    getNationStaticInfo,
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

    public function getCommandDetailTitle():string{
        $name = $this->getName();
        return "{$name}(통솔경험)";
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
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $officerLevel = $general->getVar('officer_level');
        $destCityID = null;
        if(2 <= $officerLevel && $officerLevel <= 4){
            $destCityID = $general->getVar('officer_city');
        }
        else{
            $destCityID = $this->nation['capital'];
        }
        $destCityName = CityConst::byID($destCityID)->name;

        $josaRo = JosaUtil::pick($destCityName, '로');

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaRo} 귀환했습니다. <1>$date</>");

        $exp = 70;
        $ded = 100;
        
        $general->setVar('city', $destCityID);

        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVar('leadership_exp', 1);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;
    }

    
}