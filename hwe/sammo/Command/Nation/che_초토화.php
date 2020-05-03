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

class che_초토화 extends Command\NationCommand{
    static protected $actionName = '초토화';
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
        $this->setNation(['surlimit', 'gold', 'rice', 'capital']);
        $this->setDestCity($this->arg['destCityID'], null);
        
        $this->runnableConstraints=[
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::OccupiedDestCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::SuppliedDestCity(),
            ConstraintHelper::ReqNationValue('capital', '수도', '!=', $this->destCity['city'], '수도입니다.'),
            ConstraintHelper::ReqNationValue('surlimit', '제한 턴', '==', 0, '외교제한 턴이 남아있습니다.'),
            ConstraintHelper::AllowDiplomacyStatus($this->generalObj->getNationID(), [
                2, 7
            ], '평시에만 가능합니다.'),
        ];
    }
    
    public function getCost():array{
        return [0, 0];
    }

    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 24;
    }

    public function getBrief():string{
        $commandName = $this->getName();
        $destCityName = CityConst::byID($this->arg['destCityID'])->name;
        $josaUl = JosaUtil::pick($destCityName, '을');
        return "【{$destCityName}】{$josaUl} {$commandName}";
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

        $destCity = $this->destCity;
        $destCityID = $destCity['city'];
        $destCityName = $destCity['name'];

        $nationID = $general->getNationID();
        $nationName = $this->nation['name'];

        $josaUl = JosaUtil::pick($destCityName, '을');

        $logger = $general->getLogger();
        

        $general->addExperience(-$general->getVar('experience') * 0.1, false);
        $general->addExperience(5 * ($this->getPreReqTurn() + 1));
        $general->addDedication(5 * ($this->getPreReqTurn() + 1));

        $josaYi = JosaUtil::pick($generalName, '이');
        $josaYiNation = JosaUtil::pick($nationName, '이');

        $amount = $destCity['pop'] / 5;
        foreach(['agri', 'comm', 'secu'] as $cityRes){
            $cityResMax = "{$cityRes}_max";
            $amount *= (($destCity[$cityRes] - $destCity[$cityResMax] * 0.5) / $destCity[$cityResMax]) + 1;
        }
        $amount = Util::toInt($amount);

        $db->update('general', [
            'experience'=>$db->sqleval('experience * 0.9')
        ], 'nation = %i AND officer_level >= 5 AND no!=%i', $nationID, $generalID);

        $db->update('city', [
            'trust'=>$db->sqleval('greatest(50, trust)'),
            'pop'=>$db->sqleval('greatest(pop_max*0.1, pop*0.2)'),
            'agri'=>$db->sqleval('greatest(agri_max*0.1, agri*0.2)'),
            'comm'=>$db->sqleval('greatest(comm_max*0.1, comm*0.2)'),
            'nation'=>0,
            'front'=>0,
            'conflict'=>'{}'
        ], 'city=%i', $destCityID);

        $db->update('nation', [
            'gold' => $db->sqleval('gold + %i', $amount),
            'rice' => $db->sqleval('rice + %i', $amount),
            'surlimit' => $db->sqleval('surlimit + %i', $this->getPostReqTurn()),
        ], 'nation=%i', $nationID);

        \sammo\refreshNationStaticInfo();
        \sammo\SetNationFront($nationID);
        
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaUl} 초토화했습니다. <1>$date</>");
        $logger->pushGeneralHistoryLog("<G><b>{$destCityName}</b></>{$josaUl} <M>초토화</> 명령");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>{$josaUl} <M>초토화</> 명령");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>{$josaUl} <M>초토화</>하였습니다.");
        $logger->pushGlobalHistoryLog("<S><b>【초토화】</b></><D><b>{$nationName}</b></>{$josaYiNation} <G><b>{$destCityName}</b></>{$josaUl} <M>초토화</>하였습니다.");

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
선택된 도시를 초토화 시킵니다.<br>
도시가 공백지가 되며, 도시의 인구, 내정 상태에 따라 상당량의 국고가 확보됩니다.<br>
국가의 수뇌들은 명성을 잃습니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<select class='formInput' name="destCityID" id="destCityID" size='1' style='color:white;background-color:black;'>
<?=\sammo\optionsForCities()?><br>
</select> <input type=button id="commonSubmit" value="<?=$this->getName()?>"><br>
<br>
<?php
        return ob_get_clean();
    }
}