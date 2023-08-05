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

use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;

use function sammo\searchDistance;

class che_접경귀환 extends Command\GeneralCommand{
    static protected $actionName = '접경귀환';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init()
    {

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        [$reqGold, $reqRice] = $this->getCost();

        $this->fullConditionConstraints=[
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::NotWanderingNation(),
            ConstraintHelper::NotOccupiedCity()
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

    public function run(\Sammo\RandUtil $rng):bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $cityID = $general->getCityID();
        $logger = $general->getLogger();

        $distanceList = searchDistance($cityID, 3, true);

        $occupiedCityList = new \Ds\Set($db->queryFirstColumn(
            'SELECT city FROM city WHERE nation = %i AND city IN %li AND supply = 1',
            $general->getNationID(),
            array_merge(...$distanceList)
        ));

        $nearestCityList = [];
        foreach($distanceList as $cityList){
            foreach($cityList as $cityID){
                if($occupiedCityList->contains($cityID)){
                    $nearestCityList[] = $cityID;
                }
            }
            if($nearestCityList){
                break;
            }
        }

        if(!$nearestCityList){
            $logger->pushGeneralActionLog("3칸 이내에 아국 도시가 없습니다.");
            return false;
        }

        $destCityID = $rng->choice($nearestCityList);
        $destCityName = CityConst::byID($destCityID)->name;

        $josaRo = JosaUtil::pick($destCityName, '로');
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaRo} 접경귀환했습니다.");
        $general->setVar('city', $destCityID);

        //TODO: InstantAction일때에만 설정하지 않는게 나은데..
        //$this->setResultTurn(new LastTurn(static::getName(), $this->arg));

        $general->applyDB($db);

        return true;
    }


}