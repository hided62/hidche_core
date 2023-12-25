<?php
namespace sammo\Command\UserAction;

use sammo\ActionBuff\g65_내정성공;
use \sammo\Command;
use sammo\Constraint\ConstraintHelper;

class g65_철야내정 extends Command\UserActionCommand{
    static protected $actionName = '철야내정';

    protected function argTest():bool{
        return true;
    }

    public function getBrief(): string
    {
        return '철야 내정';
    }

    public function getCommandDetailTitle(): string
    {
        $postReqTurn = $this->getPostReqTurn();
        return "2턴 간 내정 항상 성공(재사용 대기 {$postReqTurn})";
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
        $general->addInstantBuff(new g65_내정성공(), 2);

        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("2턴 간 내정을 철저히 지휘합니다.");
        return true;
    }
}