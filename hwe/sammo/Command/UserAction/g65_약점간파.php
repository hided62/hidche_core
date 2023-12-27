<?php
namespace sammo\Command\UserAction;

use sammo\ActionBuff\g65_전투순위보정;
use \sammo\Command;
use sammo\Constraint\ConstraintHelper;

class g65_약점간파 extends Command\UserActionCommand{
    static protected $actionName = '약점간파';

    protected function argTest():bool{
        return true;
    }

    public function getBrief(): string
    {
        return '약점 간파';
    }

    public function getCommandDetailTitle(): string
    {
        $postReqTurn = $this->getPostReqTurn();
        return "1턴동안 유리한 병종과 상대할 가능성 대폭 증가(재사용 대기 {$postReqTurn})";
    }

    protected function init(){
        $this->setCity();
        $this->setNation();

        $this->fullConditionConstraints = [
            ConstraintHelper::NotBeNeutral()
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
        $general->addInstantBuff(new g65_전투순위보정(), 1);

        $date = $general->getTurnTime($general::TURNTIME_HM);

        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("상대진영의 약점을 꿰뚫어 유리한 상대를 찾습니다. <1>$date</>");
        return true;
    }
}