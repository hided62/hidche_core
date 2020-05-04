<?php
namespace sammo\Command\Nation;

use \sammo\{
    DB, Util, JosaUtil,
    General, DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command,
    MessageTarget,
    Message,
    CityConst,
    CityHelper
};

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx,
    GetImageURL,
    getNationStaticInfo 
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\Event\Action;

class che_감축 extends Command\NationCommand{
    static protected $actionName = '감축';

    protected function argTest():bool{
        $this->arg = [];

        return true;
    }

    protected function init(){
        $general = $this->generalObj;

        $env = $this->env;

        if($general->getNationID()===0){
            $this->permissionConstraints=[
                ConstraintHelper::NotBeNeutral(),
            ];
            $this->fullConditionConstraints=[
                ConstraintHelper::NotBeNeutral(),
            ];
            return;
        }

        $this->setCity();
        $this->setNation(['gold', 'rice', 'capset', 'capital']);
        $this->setDestCity($this->nation['capital']);
        
        [$reqGold, $reqRice] = $this->getCost();

        $origCityLevel = CityConst::byID($this->nation['capital'])->level;

        $this->fullConditionConstraints=[
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ReqDestCityValue('level', '규모', '>', 4, '더이상 감축할 수 없습니다.'),
            ConstraintHelper::ReqDestCityValue('level', '규모', '>', $origCityLevel, '더이상 감축할 수 없습니다.')
        ];
    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();

        [$reqGold, $reqRice] = array_map('number_format', $this->getCost());
        $amount = number_format($this->env['develcost'] * 5);
        $reqTurn = $this->getPreReqTurn()+1;

        return "{$name}/{$reqTurn}턴(금 {$reqGold}, 쌀 {$reqRice} 회수)";
    }
    
    public function getCost():array{
        $amount = $this->env['develcost'] * GameConst::$expandCityCostCoef + GameConst::$expandCityDefaultCost / 2;
        
        return [$amount, $amount];
    }

    public function getPreReqTurn():int{
        return 5;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function addTermStack():bool{
        $lastTurn = $this->getLastTurn();
        $commandName = $this->getName();
        if($lastTurn->getCommand() != $commandName || $lastTurn->getArg() !== $this->arg){
            $this->setResultTurn(new LastTurn(
                $commandName,
                $this->arg,
                1,
                $this->nation['capset']
            ));
            return false;
        }

        if($lastTurn->getSeq() < $this->nation['capset']){
            //NOTE: 최근에 천도, 감축이 일어났으면 리셋됨
            $this->setResultTurn(new LastTurn(
                $commandName,
                $this->arg,
                1,
                $this->nation['capset']
            ));
            return false;
        }

        if($lastTurn->getTerm() < $this->getPreReqTurn()){
            $this->setResultTurn(new LastTurn(
                $commandName,
                $this->arg,
                $lastTurn->getTerm() + 1,
                $this->nation['capset']
            ));
            return false;
        }

        return true;
    }

    public function getBrief():string{
        $commandName = $this->getName();
        return "수도를 {$commandName}";
    }

    public function run():bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $generalID = $general->getID();
        $generalName = $general->getName();
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $year = $this->env['year'];
        $month = $this->env['month'];

        $destCity = $this->destCity;
        $destCityID = $destCity['city'];
        $destCityName = $destCity['name'];

        $nationID = $general->getNationID();
        $nationName = $this->nation['name'];

        

        $logger = $general->getLogger();
        

        $general->addExperience(5 * ($this->getPreReqTurn() + 1));
        $general->addDedication(5 * ($this->getPreReqTurn() + 1));

        $josaUl = JosaUtil::pick($destCityName, '을');
        $josaYi = JosaUtil::pick($generalName, '이');
        $josaYiNation = JosaUtil::pick($nationName, '이');

        $db->update('city', [
            'level'=>$db->sqleval('level-1'),
            'pop'=>$db->sqleval('greatest(pop - %i, %i)', GameConst::$expandCityPopIncreaseAmount, GameConst::$minAvailableRecruitPop),
            'agri'=>$db->sqleval('greatest(agri - %i, 0)', GameConst::$expandCityDevelIncreaseAmount),
            'comm'=>$db->sqleval('greatest(comm - %i, 0)', GameConst::$expandCityDevelIncreaseAmount),
            'secu'=>$db->sqleval('greatest(secu - %i, 0)', GameConst::$expandCityDevelIncreaseAmount),
            'def'=>$db->sqleval('greatest(def - %i, 0)', GameConst::$expandCityWallIncreaseAmount),
            'wall'=>$db->sqleval('greatest(wall - %i, 0)', GameConst::$expandCityWallIncreaseAmount),

            'pop_max'=>$db->sqleval('pop_max - %i', GameConst::$expandCityPopIncreaseAmount),
            'agri_max'=>$db->sqleval('agri_max - %i', GameConst::$expandCityDevelIncreaseAmount),
            'comm_max'=>$db->sqleval('comm_max - %i', GameConst::$expandCityDevelIncreaseAmount),
            'secu_max'=>$db->sqleval('secu_max - %i', GameConst::$expandCityDevelIncreaseAmount),
            'def_max'=>$db->sqleval('def_max - %i', GameConst::$expandCityWallIncreaseAmount),
            'wall_max'=>$db->sqleval('wall_max - %i', GameConst::$expandCityWallIncreaseAmount),
        ], 'city=%i', $destCityID);

        [$reqGold, $reqRice] = $this->getCost();
        $db->update('nation', [
            'capset' => $db->sqleval('capset + 1'),
            'gold' => $db->sqleval('gold + %i', $reqGold),
            'rice' => $db->sqleval('rice + %i', $reqRice),
        ], 'nation=%i', $nationID);
        
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaUl} 감축했습니다. <1>$date</>");
        $logger->pushGeneralHistoryLog("<G><b>{$destCityName}</b></>{$josaUl} <M>감축</>");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>{$josaUl} <M>감축</>");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>{$josaUl} <M>감축</>하였습니다.");
        $logger->pushGlobalHistoryLog("<M><b>【감축】</b></><D><b>{$nationName}</b></>{$josaYiNation} <G><b>{$destCityName}</b></>{$josaUl} <M>감축</>하였습니다.");

        $general->setResultTurn(new LastTurn($this->getName(), $this->arg, 0));
        $general->applyDB($db);

        return true;
    }
}