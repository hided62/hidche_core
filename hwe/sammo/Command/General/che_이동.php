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
use sammo\CityConst;



class che_이동 extends Command\GeneralCommand{
    static protected $actionName = '이동';

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();
        $this->setDestCity($this->arg['destCityID'], []);

        [$reqGold, $reqRice] = $this->getCost();
        
        $this->runnableConstraints=[
            ['NotSameCity'], 
            ['NearCity', 1],
            ['ReqGeneralGold', $reqGold],
            ['ReqGeneralRice', $reqRice],
        ];
    }

    protected function argTest():bool{
        if(!key_exists('destCityID', $this->arg)){
            return false;
        }
        if(!key_exists($this->arg['destCityID'], CityConst::all())){
            return false;
        }
        $this->arg = [
            'destCityID'=>$this->arg['destCityID']
        ];
        return true;
    }

    public function getCost():array{
        $env = $this->env;
        return [$env['develcost'], 0];
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
        $env = $this->env;

        $general = $this->generalObj;
        $date = substr($general->getVar('turntime'),11,5);

        $destCityName = $this->destCity['name'];
        $destCityID = $this->destCity['city'];
        $josaRo = JosaUtil::pick($destCityName, '로');

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaRo} 이동했습니다. <1>$date</>");

        $exp = 50;

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $general->setVar('city', $destCityID);

        if($general->getVar('level') == 12 && $this->nation['level'] == 0){
            $generalList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no!=%i', $general->getNationID(), $general->getID());
            if($generalList){
                $db->update('general', [
                    'city'=>$destCityID
                ], 'no IN %li', $generalList);
            }
            foreach($generalList as $targetGeneralID){
                $targetGeneral = General::createGeneralObjFromDB($targetGeneralID, [], 1);
                $targetLogger = new ActionLogger($targetGeneralID, $general->getNationID(), $env['year'], $env['month']);
                $targetLogger->pushGeneralActionLog("방랑군 세력이 <G><b>{$destCityName}</b></>{$josaRo} 이동했습니다.", ActionLogger::PLAIN);
                $targetLogger->flush();
            }
        }

        [$reqGold, $reqRice] = $this->getCost();
        $general->increaseVarWithLimit('gold', -$reqGold, 0);
        $general->increaseVar('experience', $exp);
        $general->increaseVar('leader2', 1);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

    }

    
}