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
    tryUniqueItemLottery
};

use \sammo\Constraint\Constraint;
use sammo\CityConst;



class che_건국 extends Command\GeneralCommand{
    static protected $actionName = '건국';

    protected function argTest():bool{
        $nationName = $this->arg['nationName']??null;
        $nationType = $this->arg['nationType']??null;
        $colorType = $this->arg['colotType']??null;

        if($nationName === null || $nationType === null || $colorType === null){
            return false;
        }

        if(!is_string($nationName) || !is_string($nationType) || !is_int($colorType)){
            return false;
        }

        return true;
    }

    protected function init(){

        $general = $this->generalObj;
        $env = $this->env;

        $this->setCity();
        $this->setNation();

        $destGeneralID = $this->arg['destGeneralID']??null;
        $destNationID = $this->arg['destNationID']??null;
        if($destGeneralID !== null){
            $this->setDestGeneral($destGeneralID);
            $this->setDestNation($this->destGeneralObj->getVar('nation'));
        }
        else{
            $this->setDestNation($destNationID, ['gennum', 'scout']);
        }

        $relYear = $env['year'] - $env['startyear'];
        
        $this->runnableConstraints=[
            ['BeNeutral'],
            ['ExistsDestNation'],
            ['AllowJoinDestNation', $relYear],
            ['AllowJoinAction']
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
        $date = substr($general->getVar('turntime'),11,5);
        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $destNation = $this->destNation;
        $gennum = $destNation['gennum'];
        $destNationID = $destNation['nation'];
        $destNationName = $destNation['name'];

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("<D>{$destNationName}</>에 임관했습니다. <1>$date</>");
        $logger->pushGeneralHistoryLog("<D><b>{$destNationName}</b></>에 임관");
        $logger->pushGlobalActionLog("{$generalName}</>{$josaYi} <D><b>{$destNationName}</b></>에 <S>임관</>했습니다.");

        if($gennum < GameConst::$initialNationGenLimit) {
            $exp = 700;
        }
        else {
            $exp = 100;
        }

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $general->setVar('nation', $destNationID);
        $general->setVar('level', 1);
        $general->setVar('belong', 1);
        
        if($this->destGeneralObj !== null){
            $general->setVar('city', $this->destGeneralObj->getCityID());
        }
        else{
            $targetCityID = $db->queryFirstField('SELECT city FROM nation WHERE nation = %i AND level=12', $destNationID);
            $general->setVar('city', $targetCityID);
        }

        $db->update('nation', [
            'gennum'=>$db->sqleval('gennum + 1')
        ], 'nation=%i', $destNationID);

        $relYear = $env['year'] - $env['startyear'];
        if($general->getVar('npc') == 1 || $relYear >= 3){
            $joinedNations = Join::decode($general->getVar('nations'));
            $joinedNations[] = $destNationID;
            $general->setVar('nations', Join::encode($joinedNations));
        }

        $general->increaseVar('experience', $exp);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    
}