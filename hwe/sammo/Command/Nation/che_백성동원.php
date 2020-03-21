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

class che_백성동원 extends Command\NationCommand{
    static protected $actionName = '백성동원';
    static public $reqArg = true;

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
        $this->setNation(['strategic_cmd_limit']);
        $this->setDestCity($this->arg['destCityID'], null);
        $this->setDestNation($this->destCity['nation']);
        
        $this->runnableConstraints=[
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::AllowDiplomacyStatus($this->generalObj->getNationID(), [
                0
            ], '전쟁중이 아닙니다.'),
            ConstraintHelper::OccupiedDestCity(),
            ConstraintHelper::AvailableStrategicCommand()
        ];
    }
    
    public function getCost():array{
        return [0, 0];
    }
    
    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        $genCount = Util::valueFit($this->nation['gennum'], GameConst::$initialNationGenLimit);
        $nextTerm = Util::round(sqrt($genCount*4)*10);    

        $nextTerm = $this->generalObj->onCalcStrategic($this->getName(), 'delay', $nextTerm);
        return $nextTerm;
    }

    public function getBrief():string{
        $commandName = $this->getName();
        $destCityName = CityConst::byID($this->arg['destCityID'])->name;
        return "【{$destCityName}】에 {$commandName}";
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
        $logger->pushGeneralActionLog("백성동원 발동! <1>$date</>");

        $general->increaseVar(
            'experience',
            $general->onCalcStat($general,
            'experience', 5 * ($this->getPreReqTurn() + 1)
        ));
        $general->increaseVar(
            'dedication',
            $general->onCalcStat($general,
            'dedication', 5 * ($this->getPreReqTurn() + 1)
        ));

        $josaYi = JosaUtil::pick($generalName, '이');

        $broadcastMessage = "<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>에 <M>백성동원</>을 하였습니다.";

        $targetGeneralList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no != %i', $nationID, $generalID);
        foreach($targetGeneralList as $targetGeneralID){
            $targetLogger = new ActionLogger($targetGeneralID, $nationID, $year, $month);
            $targetLogger->pushGeneralActionLog($broadcastMessage, ActionLogger::PLAIN);
            $targetLogger->flush();
        }

        $db->update('city', [
            'def' => $db->sqleval('GREATEST(def2 * 0.8, def)'),
            'wall' => $db->sqleval('GREATEST(wall2 * 0.8, wall)'),
        ], 'city=%i', $destCityID);

        $josaYiNation = JosaUtil::pick($nationName, '이');
        

        $logger->pushGeneralHistoryLog('<M>백성동원</>을 발동');
        $logger->pushNationalHistoryLog("<L><b>【전략】</b></><D><b>{$nationName}</b></>{$josaYiNation} <G><b>{$destCityName}</b></>에 <M>백성동원</>을 하였습니다.");

        $db->update('nation', ['strategic_cmd_limit' => $this->getPostReqTurn()], 'nation=%i', $nationID);

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
선택된 도시에 백성을 동원해 성벽을 쌓습니다.<br>
아국 도시만 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<select class='formInput' name="destCityID" id="destCityID" size='1' style='color:white;background-color:black;'>
<?=\sammo\optionsForCities()?><br>
</select> <input type=button id="commonSubmit" value="<?=$this->getName()?>"><br>
<br>
<?php
        return ob_get_clean();
    }
}