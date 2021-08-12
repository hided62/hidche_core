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
use sammo\Json;
use sammo\KVStorage;

class che_불가침수락 extends Command\NationCommand
{
    static protected $actionName = '불가침 수락';
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

        if (!key_exists('year', $this->arg) || !key_exists('month', $this->arg)) {
            return false;
        }
        $year = $this->arg['year'];
        $month = $this->arg['month'];
        if (!is_int($year) || !is_int($month)) {
            return false;
        }

        if ($month < 1 || 12 < $month) {
            return false;
        }

        if ($year < $this->env['startyear']) {
            return false;
        }

        $this->arg = [
            'destNationID' => $destNationID,
            'destGeneralID' => $destGeneralID,
            'year' => $year,
            'month' => $month,
        ];
        return true;
    }

    protected function init()
    {
        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        $this->permissionConstraints = [
            ConstraintHelper::AlwaysFail('예약 불가능 커맨드')
        ];
    }

    protected function initWithArg()
    {
        $env = $this->env;
        $relYear = $env['year'] - $env['startyear'];

        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], [], 1);
        $this->setDestGeneral($destGeneral);
        $this->setDestNation($this->arg['destNationID']);

        //NOTE: 개월에서 기한으로 바뀜
        $year = $this->arg['year'];
        $month = $this->arg['month'];

        $currentMonth = $env['year'] * 12 + $env['month'] - 1;
        $reqMonth = $year * 12 + $month - 1;

        $nationID = $this->nation['nation'];

        if ($reqMonth <= $currentMonth) {
            $this->fullConditionConstraints = [
                ConstraintHelper::AlwaysFail('이미 기한이 지났습니다.')
            ];
            return;
        }

        $this->fullConditionConstraints = [
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::ReqDestNationValue('nation', '소속', '==', $this->destGeneralObj->getNationID(), '제의 장수가 국가 소속이 아닙니다'),
            ConstraintHelper::DisallowDiplomacyBetweenStatus([
                0 => '아국과 이미 교전중입니다.',
                1 => '아국과 이미 선포중입니다.',
            ]),
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
        $year = $this->arg['year'];
        $month = $this->arg['month'];
        return "{$year}년 {$month}월까지 불가침 합의";
    }

    public function run(): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;

        $nation = $this->nation;
        $nationID = $nation['nation'];
        $nationName = $nation['name'];

        $destNation = $this->destNation;
        $destNationID = $destNation['nation'];
        $destNationName = $destNation['name'];

        $destNationStor = KVStorage::getStorage(DB::db(), $destNationID, 'nation_env');
        $destRecvAssist = $destNationStor->getValue('recv_assist')??[];
        $destRespAssist = $destNationStor->getValue('resp_assist')??[];

        $destRespAssist["n{$nationID}"] = [$nationID, $destRecvAssist["n{$nationID}"][1]??0];
        $destNationStor->setValue('resp_assist', $destRespAssist);

        $year = $this->arg['year'];
        $month = $this->arg['month'];

        $logger = $general->getLogger();
        $destLogger = $this->destGeneralObj->getLogger();

        $currentMonth = $env['year'] * 12 + $env['month'] - 1;
        $reqMonth = $year * 12 + $month - 1;

        $db->update(
            'diplomacy',
            [
                'state' => 7,
                'term' => $reqMonth - $currentMonth
            ],
            '(me=%i AND you=%i) OR (you=%i AND me=%i)',
            $nationID,
            $destNationID,
            $nationID,
            $destNationID
        );

        $josaWa = JosaUtil::pick($destNationName, '와');
        $logger->pushGeneralActionLog("<D><b>{$destNationName}</b></>{$josaWa} <C>$year</>년 <C>{$month}</>월까지 불가침에 성공했습니다.", ActionLogger::PLAIN);
        $logger->pushGeneralHistoryLog("<D><b>{$destNationName}</b></>{$josaWa} {$year}년 {$month}월까지 불가침 성공");


        $josaWa = JosaUtil::pick($nationName, '와');
        $destLogger->pushGeneralActionLog("<D><b>{$nationName}</b></>{$josaWa} <C>$year</>년 <C>{$month}</>월까지 불가침에 성공했습니다.", ActionLogger::PLAIN);
        $destLogger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>{$josaWa} {$year}년 {$month}월까지 불가침 성공");

        $general->applyDB($db);
        $destLogger->flush();

        return true;
    }
}
