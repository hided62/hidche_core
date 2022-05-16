<?php
namespace sammo\Command\General;

use \sammo\{
    DB,
    LastTurn,
    Command
};

use \sammo\Constraint\ConstraintHelper;

use function sammo\tryUniqueItemLottery;

class che_은퇴 extends Command\GeneralCommand{
    static protected $actionName = '은퇴';

    static protected $reqAge = 60;

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setNation();
        $reqAge = static::$reqAge;

        $this->fullConditionConstraints=[
            ConstraintHelper::ReqGeneralValue('age', '나이', '>=', $reqAge, "나이가 {$reqAge}세 이상이어야 합니다.")
        ];

    }

    public function getCommandDetailTitle(): string
    {
        $name = $this->getName();
        $reqAge = static::$reqAge;
        return "{$name}({$reqAge}세 이상, 2턴)";
    }

    public function getCost():array{
        return [0, 0];
    }

    public function getPreReqTurn():int{
        return 1;
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

        $logger = $general->getLogger();

        $general->rebirth();
        $logger->pushGeneralActionLog("은퇴하였습니다. <1>$date</>");

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        tryUniqueItemLottery(\sammo\genGenericUniqueRNGFromGeneral($general), $general);
        $general->applyDB($db);

        return true;
    }


}