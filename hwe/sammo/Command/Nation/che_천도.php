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

class che_천도 extends Command\NationCommand{
    static protected $actionName = '천도';
    static public $reqArg = true;

    private $cachedDist = null;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }

        if(!key_exists('destCityID', $this->arg)){
            return false;
        }
        if(CityConst::byID($this->arg['destCityID']) === null){
            return false;
        }
        $destCityID = $this->arg['destCityID'];

        $this->arg = [
            'destCityID'=>$destCityID,
        ];
        return true;
    }

    protected function init(){
        $general = $this->generalObj;

        $env = $this->env;

        $this->setCity();
        $this->setNation(['capset', 'gold', 'rice']);
        $this->setDestCity($this->arg['destCityID'], null);
        
        [$reqGold, $reqRice] = $this->getCost();

        if($this->getDistance() === null){
            $this->runnableConstraints[
                ConstraintHelper::AlwaysFail('천도 대상으로 도달할 방법이 없습니다.')
            ];
            return;
        }

        $this->runnableConstraints=[
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::OccupiedDestCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::SuppliedDestCity(),
            ConstraintHelper::NotSameDestCity(),
            ConstraintHelper::ReqNationGold(GameConst::$basegold+$reqGold),
            ConstraintHelper::ReqNationRice(GameConst::$baserice+$reqRice),
        ];
    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();

        $amount = number_format($this->env['develcost'] * 5);

        return "{$name}/1+거리×2턴(금쌀 {$amount}×2^거리)";
    }
    
    public function getCost():array{
        $amount = $this->env['develcost'] * 5;
        $amount *= 2**$this->getDistance()??50;
        
        return [$amount, $amount];
    }

    private function getDistance():?int{
        if($this->cachedDist !== null){
            return $this->cachedDist;
        }
        $srcCityID = $this->nation['capital'];
        $destCityID = $this->arg['destCityID'];
        $nationID = $this->nation['nation'];
        $distance = \sammo\calcCityDistance($srcCityID, $destCityID, [$nationID])??50;
        $this->cachedDist = $distance;
        
        return $distance;
    }
    
    public function getPreReqTurn():int{
        return 1 + $this->getDistance()*2;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function addTermStack():bool{
        $lastTurn = $this->getLastTurn();
        $commandName = $this->getName();

        $nationStor = \sammo\KVStorage::getStorage(DB::db(), 'nation_env');
        $general = $this->getGeneral();
        $nationID = $general->getNationID();
        $nationStor->setValue("last천도Trial_{$nationID}", [$general->getVar('level'), $general->getTurnTime()]);

        if($lastTurn->getCommand() != $commandName && $lastTurn->getArg() !== $this->arg){
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
        $destCityName = CityConst::byID($this->arg['destCityID'])->name;
        $josaRo = JosaUtil::pick($destCityName, '로');
        return "【{$destCityName}】{$josaRo} {$commandName}";
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

        $josaRo = JosaUtil::pick($destCityName, '로');

        $logger = $general->getLogger();
        

        $general->addExperience(5 * ($this->getPreReqTurn() + 1));
        $general->addDedication(5 * ($this->getPreReqTurn() + 1));

        $josaYi = JosaUtil::pick($generalName, '이');
        $josaYiNation = JosaUtil::pick($nationName, '이');

        $db->update('nation', [
            'capital' => $destCityID,
            'capset' => $db->sqleval('capset + 1'),
        ], 'nation=%i', $nationID);
        
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaRo} 천도했습니다. <1>$date</");
        $logger->pushGeneralHistoryLog("<G><b>{$destCityName}</b></>{$josaRo} <M>천도</>명령");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>{$josaRo} <M>천도</> 명령");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>{$josaRo} <M>천도</>를 명령하였습니다.");
        $logger->pushGlobalHistoryLog("<S><b>【천도】</b></><D><b>{$nationName}</b></>{$josaYiNation} <G><b>{$destCityName}</b></>{$josaRo} <M>천도</>하였습니다.");

        $general->setResultTurn(new LastTurn($this->getName(), $this->arg, 0));
        $general->applyDB($db);
        return true;
    }

    public function getJSFiles(): array
    {
        return [
            'js/defaultSelectCityByMap.js'
        ];
    }


    public function getForm(): string
    {
        ob_start();
?>
<?=\sammo\getMapHtml()?><br>
선택된 도시로 천도합니다.<br>
현재 수도에서 연결된 도시만 가능하며, 1+2×거리만큼의 턴이 필요합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<select class='formInput' name="destCityID" id="destCityID" size='1' style='color:white;background-color:black;'>
<?=\sammo\optionsForCities()?><br>
</select> <input type=button id="commonSubmit" value="<?=$this->getName()?>"><br>
<br>
<?php
        return ob_get_clean();
    }
}