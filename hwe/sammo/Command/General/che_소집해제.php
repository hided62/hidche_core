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

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_소집해제 extends Command\GeneralCommand{
    static protected $actionName = '소집해제';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        $this->fullConditionConstraints=[
            ConstraintHelper::ReqGeneralCrew(),
        ];

    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();

        return "{$name}(병사↓, 인구↑)";
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
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $crew = $general->getVar('crew');

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("병사들을 <R>소집해제</>하였습니다. <1>$date</>");

        $exp = 70;
        $ded = 100;

        $db->update('city', [
            'pop'=>$db->sqleval('pop + %i', $crew)
        ], 'city=%i', $general->getCityID());

        $general->setVar('crew', 0);
        $general->addExperience($exp);
        $general->addDedication($ded);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;
    }


}