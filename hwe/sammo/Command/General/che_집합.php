<?php
namespace sammo\GeneralCommand;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst, GameUnitConst,
    LastTurn,
    Command
};


use function \sammo\{
    uniqueItemEx
};

use \sammo\Constraint\Constraint;
use sammo\CityConst;



class che_집합 extends Command\GeneralCommand{
    static protected $actionName = '집합';

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();
        $this->setDestCity($this->arg['destCityID'], []);

        [$reqGold, $reqRice] = $this->getCost();
        
        $this->runnableConstraints=[
            ['NoNeutral'], 
            ['OccupiedCity'],
            ['SuppliedCity'],
            ['MustBeTroopLeader'],
            ['ReqTroopMembers'],
        ];
    }

    protected function argTest():bool{
        $this->arg = null;
        return true;
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
        $date = substr($general->getVar('turntime'),11,5);

        $cityID = $this->city['city'];
        $cityName = $this->city['name'];
        $josaRo = JosaUtil::pick($cityName, '로');

        $troopID = $general->getVar('troop');
        $troopName = $db->queryFirstField('SELECT name FROM troop WHERE troop = %i', $troopID);

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("<G><b>{$cityName}</b></>에서 집합을 실시했습니다. <1>$date</>");

        $generalList = $db->query('SELECT no, name, city, nation, level FROM general WHERE nation=%i AND troop=%i AND no!=%i', $general->getNationID(), $troopID, $general->getID());
        foreach($generalList as $targetRawGeneral){
            $targetGeneralID = $targetRawGeneral['no'];
            $targetGeneral = $this->getGeneralObjFromGeneralSource($targetGeneralID);
            if($targetGeneral === null){
                $targetGeneral = new General($targetRawGeneral, null, $env['year'], $env['month'], false);
            }
            $targetGeneral->setVar('city', $destCityID);
            $targetGeneral->getLogger()->pushGeneralActionLog("{$troopName}의 부대원들은 <G><b>{$cityName}</b></>{$josaRo} 집합되었습니다.", ActionLogger::PLAIN);
            $targetGeneral = null;
        }

        $exp = 70;
        $ded = 100;

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar('leader2', 1);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        uniqueItemEx($general->getVar('no'), $logger);
    }

    
}