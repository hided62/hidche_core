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

use function \sammo\tryUniqueItemLottery;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;



class che_집합 extends Command\GeneralCommand{
    static protected $actionName = '집합';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        [$reqGold, $reqRice] = $this->getCost();

        $this->fullConditionConstraints=[
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::MustBeTroopLeader(),
            ConstraintHelper::ReqTroopMembers(),
        ];
    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();
        return "{$name}(통솔경험)";
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
        $env = $this->env;

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $cityID = $this->city['city'];
        $cityName = $this->city['name'];
        $josaRo = JosaUtil::pick($cityName, '로');

        $troopID = $general->getID();
        $troopName = $db->queryFirstField('SELECT name FROM troop WHERE troop_leader = %i', $troopID);

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("<G><b>{$cityName}</b></>에서 집합을 실시했습니다. <1>$date</>");

        $generalList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND city!=%i AND troop=%i AND no!=%i', $general->getNationID(), $cityID, $troopID, $general->getID());
        if($generalList){
            $db->update('general', [
                'city'=>$cityID
            ], 'no IN %li', $generalList);
        }
        foreach($generalList as $targetGeneralID){
            $targetLogger = new ActionLogger($targetGeneralID, $general->getNationID(), $env['year'], $env['month']);
            $targetLogger->pushGeneralActionLog("{$troopName} 부대원들은 <G><b>{$cityName}</b></>{$josaRo} 집합되었습니다.", ActionLogger::PLAIN);
            $targetLogger->flush();
        }

        $exp = 70;
        $ded = 100;

        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVar('leadership_exp', 1);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery(\sammo\genGenericUniqueRNGFromGeneral($general), $general);
        $general->applyDB($db);

        return true;
    }


}