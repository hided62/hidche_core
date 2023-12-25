<?php
namespace sammo\Command\UserAction;

use sammo\ActionBuff\g65_징병비용무시;
use \sammo\Command;
use sammo\Constraint\ConstraintHelper;

class g65_병장기지원 extends Command\UserActionCommand{
    static protected $actionName = '병장기지원';

    protected function argTest():bool{
        return true;
    }

    public function getBrief(): string
    {
        return '병장기 지원';
    }

    public function getCommandDetailTitle(): string
    {
        $postReqTurn = $this->getPostReqTurn();
        return "이번턴의 징병/모병 비용 무시(재사용 대기 {$postReqTurn})";
    }

    protected function init(){
        $this->fullConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity()
        ];
    }

    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 60;
    }

    public function getCost():array{
        return [0, 0];
    }

    public function run(\Sammo\RandUtil $rng):bool{
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $general = $this->generalObj;
        $general->addInstantBuff(new g65_징병비용무시(), 1);

        $date = $general->getTurnTime($general::TURNTIME_HM);

        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("상인에게 병사들의 병장기를 지원받습니다. <1>$date</>");
        return true;
    }
}