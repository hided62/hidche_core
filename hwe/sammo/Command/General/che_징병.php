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



class che_징병 extends Command\GeneralCommand{
    /**
     * @var \sammo\GameUnitDetail $reqCrewType
     */
    static protected $actionName = '징병';
    static protected $costOffset = 1;
    static public $reqArg = true;

    static protected $defaultTrain;
    static protected $defaultAtmos;

    protected $reqCrew = 0;
    protected $reqCrewType;
    protected $currCrewType;
    
    static protected $isInitStatic = false;
    protected static function initStatic()
    {
        static::$defaultTrain = GameConst::$defaultTrainLow;
        static::$defaultAtmos = GameConst::$defaultAtmosLow;
    }

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        if(!key_exists('crewType', $this->arg)){
            return false;
        }
        if(!key_exists('amountCrew', $this->arg)){
            return false;
        }
        $crewType = $this->arg['crewType'];
        $amount = $this->arg['amountCrew'];

        if(!is_int($crewType)){
            return false;
        }
        if(!is_numeric($amount)){
            return false;
        }
        $amount = (int)$amount;

        if(GameUnitConst::byID($crewType) === null){
            return false;
        }
        if($amount < 0){
            return false;
        }
        $this->arg = [
            'crewType'=>$crewType,
            'amountCrew'=>$amount
        ];
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation(['tech']);

        $leadership = $general->getLeadership();
        $currCrewType = $general->getCrewTypeObj();
        $maxCrew = $leadership * 100;

        $reqCrewType = GameUnitConst::byID($this->arg['crewType']);
        if($reqCrewType->id == $currCrewType->id){
            $maxCrew -= $general->getVar('crew');
        }
        $reqCrew = Util::valueFit($this->arg['amountCrew'], 100, $maxCrew);
        $this->reqCrew = $reqCrew;
        $this->reqCrewType = $reqCrewType;
        $this->currCrewType = $currCrewType;

        [$reqGold, $reqRice] = $this->getCost();
        
        $this->runnableConstraints=[
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::ReqCityCapacity('pop', '주민', 30000 + $reqCrew),
            ConstraintHelper::ReqCityTrust(20),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
            ConstraintHelper::ReqGeneralCrewMargin($reqCrewType->id),
            ConstraintHelper::AvailableRecruitCrewType($reqCrewType->id)
        ];

    }

    public function getCost():array{
        if(!$this->isArgValid){
            return [0, 0];
        }
        $reqGold = $this->reqCrewType->costWithTech($this->nation['tech'], $this->reqCrew);
        $reqGold = $this->generalObj->onCalcDomestic('징병', 'cost', $reqGold, ['armType'=>$this->reqCrewType->armType]);
        $reqGold *= static::$costOffset;
        $reqRice = $this->reqCrew / 100;
        return [$reqGold, $reqRice];
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

        $general = $this->generalObj;
        $date = substr($general->getVar('turntime'),11,5);

        $reqCrew = $this->reqCrew;
        $reqCrewText = number_format($reqCrew);
        $reqCrewType = $this->reqCrewType;

        $currCrew = $general->getVar('crew');
        $currCrewType = $this->currCrewType;

        $crewTypeName = $reqCrewType->name;
        $josaUl = JosaUtil::pick($crewTypeName, '을');

        $logger = $general->getLogger();

        if($reqCrewType->id == $currCrewType->id && $currCrew > 0){
            $logger->pushGeneralActionLog("{$crewTypeName}{$josaUl} <C>{$reqCrewText}</>명을 추가{$this->getName()}했습니다. <1>$date</>");
            $train = ($currCrew * $general->getVar('train') + $reqCrew * static::$defaultTrain) / ($currCrew + $reqCrew);
            $atmos = ($currCrew * $general->getVar('atmos') + $reqCrew * static::$defaultAtmos) / ($currCrew + $reqCrew);

            $general->increaseVar('crew', $reqCrew);
            $general->setVar('train', $train);
            $general->setVar('atmos', $atmos);
        }
        else{
            $logger->pushGeneralActionLog("{$crewTypeName}{$josaUl} <C>{$reqCrewText}</>명을 {$this->getName()}했습니다. <1>$date</>");
            $general->setVar('crewtype', $reqCrewType->id);
            $general->setVar('crew', $reqCrew);
            $general->setVar('train', static::$defaultTrain);
            $general->setVar('atmos', static::$defaultAtmos);
        }

        $newTrust = Util::valueFit($this->city['trust'] - ($reqCrew / $this->city['pop']) / static::$costOffset * 100, 0);

        $db->update('city', [
            'trust'=>$newTrust,
            'pop'=>$this->city['pop'] - $reqCrew
        ], 'city=%i', $general->getCityID());
        
        $exp = Util::round($reqCrew / 100);
        $ded = Util::round($reqCrew / 100);

        $exp = $general->onCalcStat($general, 'experience', $exp);
        $ded = $general->onCalcStat($general, 'dedication', $ded);

        $general->addDex($general->getCrewTypeObj(), $reqCrew / 100, false);

        [$reqGold, $reqRice] = $this->getCost();

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVarWithLimit('gold', -$reqGold, 0);
        $general->increaseVarWithLimit('rice', -$reqRice, 0);
        $general->increaseVar('leadership2', 1);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    
}