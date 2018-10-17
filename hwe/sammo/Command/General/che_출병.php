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
    uniqueItemEx,
    processWar
};

use \sammo\Constraint\Constraint;
use sammo\CityConst;



class che_출병 extends Command\GeneralCommand{
    static protected $actionName = '출병';

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation(['war', 'gennum', 'tech', 'gold', 'rice']);
        $this->setDestCity($this->arg['destCityID'], []);
        $this->setDestNation(['nation' ,'level','name','capital','gennum','tech','type','gold','rice']);

        [$reqGold, $reqRice] = $this->getCost();
        
        $this->runnableConstraints=[
            ['NotOpeningPart'],
            ['NearCity', 1],
            ['NoNeutral'],
            ['OccupiedCity'],
            ['ReqGeneralCrew'],
            ['ReqGeneralRice', $reqRice],
            ['AllowWar'],
            ['BattleGroundCity', 1],
        ];
    }

    protected function argTest():bool{
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

    public function getCost():array{
        return [0, Util::round($this->general->getVar('crew')/100)];
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
        $josaRo = JosaUtil::pick($destCityName, '로');

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

        uniqueItemEx($general->getID(), $logger);
        
        return true;
    }

    
}