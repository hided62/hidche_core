<?php
namespace sammo\Command\UserAction;

use sammo\ActionBuff\g65_내정성공;
use \sammo\Command;
use sammo\Constraint\ConstraintHelper;

class g65_필중계략 extends Command\UserActionCommand{
    static protected $actionName = '필중계략';

    protected function argTest():bool{
        return true;
    }

    public function getBrief(): string
    {
        return '필중계략';
    }

    public function getCommandDetailTitle(): string
    {
        $postReqTurn = $this->getPostReqTurn();
        return "이번 턴의 계략 항상 성공(재사용 대기 {$postReqTurn})";
    }

    protected function init(){
        $this->setCity();
        $this->setNation();

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
        $general->addInstantBuff(new g65_내정성공(), 2);

        $date = $general->getTurnTime($general::TURNTIME_HM);

        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("이번 턴의 계략을 집중합니다. <1>$date</>");
        return true;
    }
}