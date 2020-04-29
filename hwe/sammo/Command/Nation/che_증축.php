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
    CityConst
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

class che_증축 extends Command\NationCommand{
    static protected $actionName = '증축';
    static public $reqArg = false;

    protected function argTest():bool{
        $this->arg = [];

        return true;
    }

    protected function init(){
        $general = $this->generalObj;

        $env = $this->env;

        if($general->getNationID()===0){
            $this->reservableConstraints=[
                ConstraintHelper::NotBeNeutral(),
            ];
            $this->runnableConstraints=[
                ConstraintHelper::NotBeNeutral(),
            ];
            return;
        }

        $this->setCity();
        $this->setNation(['gold', 'rice', 'capset', 'capital']);
        $this->setDestCity($this->nation['capital'], null);
        
        [$reqGold, $reqRice] = $this->getCost();

        $this->runnableConstraints=[
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ReqDestCityValue('level', '규모', '>', 3, '수진, 진, 관문에서는 불가능합니다.'),
            ConstraintHelper::ReqDestCityValue('level', '규모', '<', 7, '더이상 증축할 수 없습니다.'),
            ConstraintHelper::ReqNationGold(GameConst::$basegold+$reqGold),
            ConstraintHelper::ReqNationRice(GameConst::$baserice+$reqRice),
        ];
    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();

        [$reqGold, $reqRice] = array_map('number_format', $this->getCost());
        $amount = number_format($this->env['develcost'] * 5);
        $reqTurn = $this->getPreReqTurn()+1;

        return "{$name}/{$reqTurn}턴(금 {$reqGold}, 쌀 {$reqRice})";
    }
    
    public function getCost():array{
        $amount = $this->env['develcost'] * GameConst::$expandCityCostCoef + GameConst::$expandCityDefaultCost;
        
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
            \sammo\LogText('증축', '여기서 걸려?');
            $this->setResultTurn(new LastTurn(
                $commandName,
                $this->arg,
                1,
                $this->nation['capset']
            ));
            return false;
        }

        if($lastTurn->getSeq() < $this->nation['capset']){
            //NOTE: 최근에 천도, 증축이 일어났으면 리셋됨
            \sammo\LogText('증축', '으으음?'.$this->nation['capset'].','.$lastTurn->getSeq());
            $this->setResultTurn(new LastTurn(
                $commandName,
                $this->arg,
                1,
                $this->nation['capset']
            ));
            return false;
        }

        if($lastTurn->getTerm() < $this->getPreReqTurn()){
            \sammo\LogText('증축', '잘된다는데?');
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
        if(!$this->isRunnable()){
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
            'level'=>$db->sqleval('level+1'),
            'pop_max'=>$db->sqleval('pop_max + %i', GameConst::$expandCityPopIncreaseAmount),
            'agri_max'=>$db->sqleval('agri_max + %i', GameConst::$expandCityDevelIncreaseAmount),
            'comm_max'=>$db->sqleval('comm_max + %i', GameConst::$expandCityDevelIncreaseAmount),
            'secu_max'=>$db->sqleval('secu_max + %i', GameConst::$expandCityDevelIncreaseAmount),
            'def_max'=>$db->sqleval('def_max + %i', GameConst::$expandCityWallIncreaseAmount),
            'wall_max'=>$db->sqleval('wall_max + %i', GameConst::$expandCityWallIncreaseAmount),
        ], 'city=%i', $destCityID);

        [$reqGold, $reqRice] = $this->getCost();
        $db->update('nation', [
            'capset' => $db->sqleval('capset + 1'),
            'gold' => $db->sqleval('gold - %i', $reqGold),
            'rice' => $db->sqleval('rice - %i', $reqRice),
        ], 'nation=%i', $nationID);
        
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaUl} 증축했습니다. <1>$date</>");
        $logger->pushGeneralHistoryLog("<G><b>{$destCityName}</b></>{$josaUl} <M>증축</>");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>{$josaUl} <M>증축</>");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>{$josaUl} <M>증축</>하였습니다.");
        $logger->pushGlobalHistoryLog("<C><b>【증축】</b></><D><b>{$nationName}</b></>{$josaYiNation} <G><b>{$destCityName}</b></>{$josaUl} <M>증축</>하였습니다.");

        $general->setResultTurn(new LastTurn($this->getName(), $this->arg, 0));
        $general->applyDB($db);

        return true;
    }
}