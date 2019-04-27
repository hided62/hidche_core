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
    tryUniqueItemLottery,
    processWar
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;



class che_출병 extends Command\GeneralCommand{
    static protected $actionName = '출병';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        if(!key_exists('destCityID', $this->arg)){
            return false;
        }
        if(!key_exists($this->arg['destCityID'], CityConst::all())){
            return false;
        }
        $this->arg = [
            'destCityID'=>$this->arg['destCityID']
        ];
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation(['war', 'gennum', 'tech', 'gold', 'rice']);
        $this->setDestCity($this->arg['destCityID'], ['city', 'name', 'nation']);

        [$reqGold, $reqRice] = $this->getCost();
        $relYear = $this->env['year'] - $this->env['startyear'];
        
        $this->runnableConstraints=[
            ConstraintHelper::NotOpeningPart($relYear),
            ConstraintHelper::NearCity(1),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::ReqGeneralCrew(),
            ConstraintHelper::ReqGeneralRice($reqRice),
            ConstraintHelper::AllowWar(),
            ConstraintHelper::BattleGroundCity(true),
        ];
    }

    public function getCost():array{
        return [0, Util::round($this->generalObj->getVar('crew')/100)];
    }
    
    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function getFailString():string{
        $commandName = $this->getName();
        $failReason = $this->testRunnable();
        if($failReason === null){
            throw new \RuntimeException('실행 가능한 커맨드에 대해 실패 이유를 수집');
        }
        $destCityName = CityConst::byID($this->arg['destCityID'])->name;
        $josaRo = JosaUtil::pick($destCityName, '로');
        return "{$failReason} <G><b>{$destCityName}</b></>{$josaRo} {$commandName} 실패.";
    }

    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $attackerNationID = $general->getNationID();
        $defenderNationID = $this->destCity['nation'];
        $date = substr($general->getVar('turntime'),11,5);

        $defenderCityName = $this->destCity['name'];
        $defenderCityID = $this->destCity['city'];
        $josaRo = JosaUtil::pick($defenderCityName, '로');

        $logger = $general->getLogger();

        if($attackerNationID == $defenderNationID){
            $logger->pushGeneralActionLog("본국입니다. <G><b>{$defenderCityName}</b></>{$josaRo} 으로 이동합니다. <1>$date</>");    
            $this->alternative = new che_이동($general, $this->env, $this->arg);
            return false;
        }

        $db->update('city', [
            'state'=>43,
            'term'=>3
        ], 'city=%i', $defenderCityID);

        $this->destCity['state'] = 43;
        $this->destCity['term'] = 3;

        $general->addDex($general->getCrewTypeObj(), $general->getVar('crew')/100);
        

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);

        //TODO: 장기적으로 통합해야함
        processWar($general->getRaw(), $this->destCity);

        tryUniqueItemLottery($general);
        $general->applyDB($db);
        
        return true;
    }

    
}