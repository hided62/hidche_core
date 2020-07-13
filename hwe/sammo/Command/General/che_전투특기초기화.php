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


use function \sammo\{
    tryUniqueItemLottery
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;



class che_전투특기초기화 extends Command\GeneralCommand{
    static protected $actionName = '전투 특기 초기화';
    static protected $specialType = 'special2';
    static protected $speicalAge = 'specage2';
    static protected $specialText = '전투 특기';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){
        $this->minConditionConstraints=[
            ConstraintHelper::ReqGeneralValue(static::$specialType, static::$specialText, '!=', 'None', '특기가 없습니다.'),
        ];
        
        $this->fullConditionConstraints=[
            ConstraintHelper::ReqGeneralValue(static::$specialType, static::$specialText, '!=', 'None', '특기가 없습니다.')
        ];
    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();
        if($this->getPreReqTurn()){
            $reqTurn = ($this->getPreReqTurn()+1).'턴, ';
        }
        else{
            $reqTurn = 0;
        }

        $title = "{$name}({$reqTurn}5년마다 1회)";
        return $title;
    }

    public function getCost():array{
        return [0, 0];
    }
    
    public function getPreReqTurn():int{
        return 1;
    }

    public function getPostReqTurn():int{
        return 60;
    }

    public function getTermString():string{
        $term = $this->getResultTurn()->getTerm();
        $termMax = $this->getPreReqTurn() + 1;
        return "새로운 적성을 찾는 중... ({$term}/{$termMax})";
    }

    public function run():bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $oldTypeKey = 'prev_types_'.static::$specialType;
        $specialName = static::$specialText;

        $env = $this->env;
        
        $yearMonth = Util::joinYearMonth($env['year'], $env['month']);
        $oldSpecialList = $general->getAuxVar($oldTypeKey)??[];
        $oldSpecialList[] = $general->getVar(static::$specialType);
        $general->setAuxVar($oldTypeKey, $oldSpecialList);

        $general->setVar(static::$specialType, 'None');
        $general->setVar(static::$speicalAge, $general->getVar('age') + 1);

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("새로운 {$specialName}를 가질 준비가 되었습니다. <1>$date</>");

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    
}