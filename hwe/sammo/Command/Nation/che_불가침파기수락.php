<?php

namespace sammo\Command\Nation;

use \sammo\DB;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\General;
use \sammo\DummyGeneral;
use \sammo\ActionLogger;
use \sammo\GameConst;
use \sammo\LastTurn;
use \sammo\GameUnitConst;
use \sammo\Command;
use \sammo\MessageTarget;
use \sammo\DiplomaticMessage;
use \sammo\Message;

use function \sammo\getNationStaticInfo;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\RandUtil;

class che_불가침파기수락 extends Command\NationCommand
{
    static protected $actionName = '불가침 파기 수락';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }

        if (!key_exists('destNationID', $this->arg)) {
            return false;
        }
        $destNationID = $this->arg['destNationID'];
        if (!is_int($destNationID)) {
            return false;
        }
        if ($destNationID < 1) {
            return false;
        }

        if (!key_exists('destGeneralID', $this->arg)) {
            return false;
        }
        $destGeneralID = $this->arg['destGeneralID'];
        if (!is_int($destGeneralID)) {
            return false;
        }
        if ($destGeneralID <= 0) {
            return false;
        }
        if ($destGeneralID == $this->generalObj->getID()) {
            return false;
        }

        $this->arg = [
            'destNationID' => $destNationID,
            'destGeneralID' => $destGeneralID,
        ];
        return true;
    }

    protected function init()
    {
        $this->setCity();
        $this->setNation();

        $this->permissionConstraints = [
            ConstraintHelper::AlwaysFail('예약 불가능 커맨드')
        ];
    }

    protected function initWithArg()
    {
        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], [], 1);
        $this->setDestGeneral($destGeneral);
        $this->setDestNation($this->arg['destNationID']);


        $this->fullConditionConstraints = [
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::ReqDestNationValue('nation', '소속', '==', $this->destGeneralObj->getNationID(), '제의 장수가 국가 소속이 아닙니다'),
            ConstraintHelper::AllowDiplomacyBetweenStatus(
                [7],
                '불가침 중인 상대국에게만 가능합니다.'
            ),
        ];
    }

    public function canDisplay():bool{
        return false;
    }

    public function getCost(): array
    {
        return [0, 0];
    }

    public function getPreReqTurn(): int
    {
        return 0;
    }

    public function getPostReqTurn(): int
    {
        return 0;
    }

    public function getBrief(): string
    {
        $commandName = $this->getName();
        $destNationName = getNationStaticInfo($this->arg['destNationID'])['name'];
        return "{$destNationName}국과 불가침 파기 합의";
    }

    public function run(RandUtil $rng): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $generalName = $general->getName();

        $nation = $this->nation;
        $nationID = $nation['nation'];
        $nationName = $nation['name'];

        $destNation = $this->destNation;
        $destNationID = $destNation['nation'];
        $destNationName = $destNation['name'];

        $logger = $general->getLogger();
        $destLogger = $this->destGeneralObj->getLogger();

        $db->update(
            'diplomacy',
            [
                'state' => 2,
                'term' => 0
            ],
            '(me=%i AND you=%i) OR (you=%i AND me=%i)',
            $nationID,
            $destNationID,
            $nationID,
            $destNationID
        );

        $josaYiGeneral = JosaUtil::pick($generalName, '이');
        $josaYiNation = JosaUtil::pick($nationName, '이');

        $josaWa = JosaUtil::pick($destNationName, '와');
        $logger->pushGeneralActionLog("<D><b>{$destNationName}</b></>{$josaWa}의 불가침을 파기했습니다.", ActionLogger::PLAIN);
        $logger->pushGeneralHistoryLog("<D><b>{$destNationName}</b></>{$josaWa}의 불가침 파기 수락");

        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYiGeneral} <D><b>{$destNationName}</b></>{$josaWa}의 불가침 조약을 <M>파기</> 하였습니다.");
        $logger->pushGlobalHistoryLog("<Y><b>【종전】</b></><D><b>{$nationName}</b></>{$josaYiNation} <D><b>{$destNationName}</b></>{$josaWa}의 불가침 조약을 <M>파기</> 하였습니다.");


        $josaWa = JosaUtil::pick($nationName, '와');
        $destLogger->pushGeneralActionLog("<D><b>{$nationName}</b></>{$josaWa}의 불가침 파기에 성공했습니다.", ActionLogger::PLAIN);
        $destLogger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>{$josaWa}의 불가침 파기 성공");

        $general->applyDB($db);
        $destLogger->flush();

        return true;
    }
}
