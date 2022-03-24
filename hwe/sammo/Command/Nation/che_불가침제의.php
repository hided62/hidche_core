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

use function \sammo\getAllNationStaticInfo;
use function \sammo\GetImageURL;
use function \sammo\getNationStaticInfo;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_불가침제의 extends Command\NationCommand
{
    static protected $actionName = '불가침 제의';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        //NOTE: 멸망 직전에 턴을 넣을 수 있으므로, 존재하지 않는 국가여도 argTest에서 바로 탈락시키지 않음
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

        $this->minConditionConstraints = [
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(),
        ];
    }

    protected function initWithArg()
    {
        $this->setDestNation($this->arg['destNationID'], null);

        //NOTE: 개월에서 기한으로 바뀜
        $year = $this->arg['year'];
        $month = $this->arg['month'];

        $env = $this->env;

        $relYear = $env['year'] - $env['startyear'];
        $currentMonth = $env['year'] * 12 + $env['month'] - 1;
        $reqMonth = $year * 12 + $month - 1;

        $nationID = $this->nation['nation'];

        if ($reqMonth < $currentMonth + 6) {
            $this->permissionConstraints = [
                ConstraintHelper::AlwaysFail('기한은 6개월 이상이어야 합니다.')
            ];

            $this->fullConditionConstraints = [
                ConstraintHelper::AlwaysFail('기한은 6개월 이상이어야 합니다.')
            ];
            return;
        }

        $this->fullConditionConstraints = [
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::DisallowDiplomacyBetweenStatus([
                0 => '아국과 이미 교전중입니다.',
                1 => '아국과 이미 선포중입니다.',
            ]),
        ];
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
        return "【{$destNationName}】에게 {$year}년 {$month}월까지 {$commandName}";
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
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $nation = $this->nation;
        $nationID = $nation['nation'];
        $nationName = $nation['name'];

        $destNation = $this->destNation;
        $destNationID = $destNation['nation'];
        $destNationName = $destNation['name'];

        $year = $this->arg['year'];
        $month = $this->arg['month'];

        $logger = $general->getLogger();
        $destLogger = new ActionLogger(0, $destNationID, $env['year'], $env['month']);

        $logger->pushGeneralActionLog("<D><b>{$destNationName}</b></>으로 불가침 제의 서신을 보냈습니다.<1>$date</>");

        // 상대에게 발송
        $src = new MessageTarget(
            $general->getID(),
            $general->getName(),
            $nationID,
            $nationName,
            $nation['color'],
            GetImageURL($general->getVar('imgsvr'), $general->getVar('picture'))
        );
        $dest = new MessageTarget(
            0,
            '',
            $destNationID,
            $destNationName,
            $destNation['color']
        );

        $now = new \DateTime($date);
        $validUntil = new \DateTime($date);
        $validMinutes = max(30, $env['turnterm'] * 3);
        $validUntil->add(new \DateInterval("PT{$validMinutes}M"));

        $josaWa = JosaUtil::pick($nationName, '와');

        $msg = new DiplomaticMessage(
            Message::MSGTYPE_DIPLOMACY,
            $src,
            $dest,
            "{$nationName}{$josaWa} {$year}년 {$month}월까지 불가침 제의 서신",
            $now,
            $validUntil,
            [
                'action' => DiplomaticMessage::TYPE_NO_AGGRESSION,
                'year' => $year,
                'month' => $month,
            ]
        );
        $msg->send();

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);
        $destLogger->flush();

        return true;
    }


    public function exportJSVars(): array
    {
        $generalObj = $this->generalObj;
        $nationID = $generalObj->getNationID();
        $nationList = [];
        $testTurn = new LastTurn($this->getName(), null, $this->getPreReqTurn());
        $currYear = $this->env['year'];

        foreach (getAllNationStaticInfo() as $destNation) {
            $testCommand = new static($generalObj, $this->env, $testTurn, [
                'destNationID' => $destNation['nation'],
                'year' => $currYear + 2,
                'month' => 1
            ]);

            $nationTarget = [
                'id' => $destNation['nation'],
                'name' => $destNation['name'],
                'color' => $destNation['color'],
                'power' => $destNation['power'],
            ];
            if (!$testCommand->hasFullConditionMet()) {
                $nationTarget['notAvailable'] = true;
            }
            if ($destNation['nation'] == $nationID) {
                $nationTarget['notAvailable'] = true;
            }

            $nationList[] = $nationTarget;
        }
        return [
            'mapTheme' => \sammo\getMapTheme(),
            'procRes' => [
                'nationList' => $nationList,
                'startYear' => $this->env['startyear'],
                'minYear' => $this->env['year'] + 1,
                'maxYear' => $this->env['year'] + 20,
                'month' => $this->env['month'],
            ],
        ];
    }
}
