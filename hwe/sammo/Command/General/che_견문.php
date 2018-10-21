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
use \sammo\TextDecoration\SightseeingMessage;


class che_견문 extends Command\GeneralCommand{
    static protected $actionName = '견문';
    
    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->runnableConstraints=[
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

    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = substr($general->getVar('turntime'),11,5);

        $sightseeing = new SightseeingMessage();

        [$type, $text] = $sightseeing->pickAction();

        $exp = 0;

        if($type & SightseeingMessage::IncExp){
            $exp += 30;
        }
        if($type & SightseeingMessage::IncHeavyExp){
            $exp += 60;
        }
        if($type & SightseeingMessage::IncLeadership){
            $general->increaseVar('leader2', 2);
        }
        if($type & SightseeingMessage::IncPower){
            $general->increaseVar('power2', 2);
        }
        if($type & SightseeingMessage::IncIntel){
            $general->increaseVar('intel2', 2);
        }
        if($type & SightseeingMessage::IncGold){
            $general->increaseVar('gold', 300);
            $text = str_replace(':goldAmount:', '300', $text);
        }
        if($type & SightseeingMessage::IncRice){
            $general->increaseVar('rice', 300);
            $text = str_replace(':riceAmount:', '300', $text);
        }
        if($type & SightseeingMessage::DecGold){
            $general->increaseVarWithLimit('gold', -200, 0);
            $text = str_replace(':goldAmount:', '200', $text);
        }
        if($type & SightseeingMessage::DecRice){
            $general->increaseVarWithLimit('rice', -200, 0);
            $text = str_replace(':riceAmount:', '200', $text);
        }
        if($type & SightseeingMessage::Wounded){
            $general->increaseVarWithLimit('injury', Util::randRangeInt(10, 20), null, 80);
        }
        if($type & SightseeingMessage::HeavyWounded){
            $general->increaseVarWithLimit('injury', Util::randRangeInt(20, 50), null, 80);
        }

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("{$text} <1>$date</>");

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);

        $general->increaseVar('experience', $exp);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;
    }

    
}