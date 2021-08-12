<?php

namespace sammo\Command\Nation;

use \sammo\{
    DB,
    Util,
    JosaUtil,
    General,
    DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command,
    MessageTarget,
    DiplomaticMessage,
    Message,
};

use function \sammo\getDomesticExpLevelBonus;
use function \sammo\CriticalRatioDomestic;
use function \sammo\CriticalScoreEx;
use function \sammo\getAllNationStaticInfo;
use function \sammo\getNationStaticInfo;
use function \sammo\GetImageURL;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_종전수락 extends Command\NationCommand
{
    static protected $actionName = '종전 수락';
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
        $general = $this->generalObj;

        $env = $this->env;
        $relYear = $env['year'] - $env['startyear'];

        $this->setCity();
        $this->setNation();

        
        $nationID = $this->nation['nation'];

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
                [0, 1],
                '상대국과 선포, 전쟁중이지 않습니다.'
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
        return "{$destNationName}국과 종전 합의";
    }

    public function run(): bool
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

        \sammo\SetNationFront($nationID);
        \sammo\SetNationFront($destNationID);

        $josaYiGeneral = JosaUtil::pick($generalName, '이');
        $josaYiNation = JosaUtil::pick($nationName, '이');

        $josaWa = JosaUtil::pick($destNationName, '와');
        $logger->pushGeneralActionLog("<D><b>{$destNationName}</b></>{$josaWa} 종전에 합의했습니다.", ActionLogger::PLAIN);
        $logger->pushGeneralHistoryLog("<D><b>{$destNationName}</b></>{$josaWa} 종전 수락");

        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYiGeneral} <D><b>{$destNationName}</b></>{$josaWa} <M>종전 합의</> 하였습니다.");
        $logger->pushGlobalHistoryLog("<Y><b>【종전】</b></><D><b>{$nationName}</b></>{$josaYiNation} <D><b>{$destNationName}</b></>{$josaWa} <M>종전 합의</> 하였습니다.");


        $josaWa = JosaUtil::pick($nationName, '와');
        $destLogger->pushGeneralActionLog("<D><b>{$nationName}</b></>{$josaWa} 종전에 성공했습니다.", ActionLogger::PLAIN);
        $destLogger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>{$josaWa} 종전 성공");

        $general->applyDB($db);
        $destLogger->flush();

        return true;
    }
}
