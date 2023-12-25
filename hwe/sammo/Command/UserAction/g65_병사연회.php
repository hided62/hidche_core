<?php
namespace sammo\Command\UserAction;

use sammo\ActionBuff\g65_사기40;
use \sammo\Command;
use \sammo\Util;
use \sammo\JosaUtil;
use sammo\LastTurn;

class g65_병사연회 extends Command\UserActionCommand{
    static protected $actionName = '병사연회';

    protected function argTest():bool{
        return true;
    }

    public function getBrief(): string
    {
        return '병사 연회';
    }

    public function getCommandDetailTitle(): string
    {
        $postReqTurn = $this->getPostReqTurn();
        return "3턴간 사기 +40(재사용 대기 {$postReqTurn})";
    }

    protected function init(){
        //아무것도 하지 않음
        $this->fullConditionConstraints=[];

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

        $this->generalObj->addInstantBuff(new g65_사기40(), 3);
        $logger = $this->generalObj->getLogger();
        $logger->pushGeneralActionLog("병사에게 연회를 열어 3턴간 사기가 40 상승합니다.");
        return true;
    }
}