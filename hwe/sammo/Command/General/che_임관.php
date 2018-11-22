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
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;



class che_임관 extends Command\GeneralCommand{
    static protected $actionName = '임관';
    static public $reqArg = true;

    protected function argTest():bool{
        $destNationID = $this->arg['destNationID']??null;
        $destGeneralID = $this->arg['destGeneralID']??null;

        if($destGeneralID === null && $destNationID === null){
            return false;
        }

        if($destGeneralID !== null && $destNationID !== null){
            return false;
        }
        
        if ($destNationID !== null) {
            if(!is_int($destNationID)){
                return false;
            }
            if($destNationID < 1){
                return false;
            }

            $this->arg = [
                'destNationID' => $destNationID
            ];
        }
        else{
            if(!is_int($destGeneralID)){
                return false;
            }
            if($destGeneralID < 1){
                return false;
            }
            if($destGeneralID == $this->generalObj->getID()){
                return false;
            }

            $this->arg = [
                'destGeneralID' => $destGeneralID
            ];
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
            ConstraintHelper::ReqEnvValue('join_mode', '==', 'onlyRandom', '랜덤 임관만 가능합니다'),
            ConstraintHelper::BeNeutral(),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::AllowJoinDestNation($relYear),
            ConstraintHelper::AllowJoinAction()
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