<?php
namespace sammo\Command\UserAction;

use sammo\ActionBuff\g65_징병인구무시;
use \sammo\Command;
use sammo\Constraint\ConstraintHelper;

class g65_입대독려 extends Command\UserActionCommand{
    static protected $actionName = '입대독려';

    protected function argTest():bool{
        return true;
    }

    public function getBrief(): string
    {
        return '입대 독려';
    }

    public function getCommandDetailTitle(): string
    {
        $postReqTurn = $this->getPostReqTurn();
        return "이번턴의 징병/모병 인구 무시(재사용 대기 {$postReqTurn})";
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
        $general->addInstantBuff(new g65_징병인구무시(), 1);

        $date = $general->getTurnTime($general::TURNTIME_HM);

        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("성 밖의 주민들에게 입대를 요청합니다. <1>$date</>");
        return true;
    }
}