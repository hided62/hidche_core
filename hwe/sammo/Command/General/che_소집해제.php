<?php
namespace sammo\GeneralCommand;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst, GameUnitConst,
    LastTurn,
    Command
};


use function \sammo\{
    uniqueItemEx
};

use \sammo\Constraint\Constraint;



class che_소집해제 extends Command\GeneralCommand{
    static protected $actionName = '소집해제';

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();
        
        $this->runnableConstraints=[
            ['ReqGeneralCrew'],
        ];

    }

    protected function argTest():bool{
        $this->arg = null;
        return true;
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

    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = substr($general->getVar('turntime'),11,5);

        $crew = $general->getVar('crew');

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("병사들을 <R>소집해제</>하였습니다. <1>$date</>");

        $exp = 70;
        $ded = 100;

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

        $db->update('city', [
            'pop'=>$db->sqleval('pop + %i', $crew)
        ], 'city=%i', $general->getCityID());

        $general->setVar('crew', 0);
        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;
    }

    
}