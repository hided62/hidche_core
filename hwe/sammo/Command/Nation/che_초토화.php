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
    CityInitialDetail
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

        $this->minConditionConstraints=[
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ReqNationValue('surlimit', '제한 턴', '==', 0, '외교제한 턴이 남아있습니다.'),
        ];
    }

    protected function initWithArg()
    {
        $this->setDestCity($this->arg['destCityID']);

        $this->fullConditionConstraints=[
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::OccupiedDestCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::SuppliedDestCity(),
            ConstraintHelper::ReqNationValue('capital', '수도', '!=', $this->destCity['city'], '수도입니다.'),
            ConstraintHelper::ReqNationValue('surlimit', '제한 턴', '==', 0, '외교제한 턴이 남아있습니다.'),
            ConstraintHelper::DisallowDiplomacyStatus($this->generalObj->getNationID(), [
                0 => '평시에만 가능합니다.',
            ], ),
        ];
    }

    public function getCost():array{
        return [0, 0];
    }

    public function getPreReqTurn():int{
        return 2;
    }

    public function getPostReqTurn():int{
        //NOTE: 자체 postReqTurn 사용
        return 24;
    }

    public function getNextAvailableTurn():?int{
        return null;
    }

    public function setNextAvailable(?int $yearMonth=null){
        return;
    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();

        $reqTurn = $this->getPreReqTurn()+1;

        return "{$name}/{$reqTurn}턴(공백지화, 금쌀 회수, 수뇌진 명성하락)";
    }

    public function getBrief():string{
        $commandName = $this->getName();
        $destCityName = CityConst::byID($this->arg['destCityID'])->name;
        $josaUl = JosaUtil::pick($destCityName, '을');
        return "【{$destCityName}】{$josaUl} {$commandName}";
    }

    public function calcReturnAmount(array $destCity):int{
        $amount = $destCity['pop'] / 5;
        foreach(['agri', 'comm', 'secu'] as $cityRes){
            $cityResMax = "{$cityRes}_max";
            $amount *= (($destCity[$cityRes] - $destCity[$cityResMax] * 0.5) / $destCity[$cityResMax]) + 0.8;
        }
        return Util::toInt($amount);
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

        $amount = $this->calcReturnAmount($destCity);

        $db->update('general', [
            'experience'=>$db->sqleval('experience * 0.9')
        ], 'nation = %i AND officer_level >= 5 AND no!=%i', $nationID, $generalID);

        $db->update('general', [
            'betray'=>$db->sqleval('betray + 1')
        ], 'nation = %i AND no!=%i', $nationID, $generalID);
        $general->increaseVar('betray', 1);

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

        $general->increaseInheritancePoint('active_action', 1);
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaUl} 초토화했습니다. <1>$date</>");
        $logger->pushGeneralHistoryLog("<G><b>{$destCityName}</b></>{$josaUl} <M>초토화</> 명령");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>{$josaUl} <M>초토화</> 명령");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>{$josaUl} <M>초토화</>하였습니다.");
        $logger->pushGlobalHistoryLog("<S><b>【초토화】</b></><D><b>{$nationName}</b></>{$josaYiNation} <G><b>{$destCityName}</b></>{$josaUl} <M>초토화</>하였습니다.");

        $this->setResultTurn(new LastTurn($this->getName(), $this->arg, 0));
        $general->applyDB($db);
        return true;
    }

    public function exportJSVars(): array
    {
        return [
            'procRes' => [
                'cities' => \sammo\JSOptionsForCities(),
                'distanceList' => new \stdClass(),
            ],
        ];
    }
}