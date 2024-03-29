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
use \sammo\Message;
use \sammo\MessageTarget;

use function \sammo\getAllNationStaticInfo;
use function \sammo\getNationStaticInfo;
use function \sammo\GetImageURL;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\Enums\MessageType;

class che_선전포고 extends Command\NationCommand
{
    static protected $actionName = '선전포고';
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

        $this->arg = [
            'destNationID' => $destNationID
        ];
        return true;
    }

    protected function init()
    {
        $general = $this->generalObj;

        $env = $this->env;


        $this->setCity();
        $this->setNation();

        $startYear = $this->env['startyear'];
        $this->minConditionConstraints = [
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ReqEnvValue('year', '>=', $startYear + 1, '초반제한 해제 2년전부터 가능합니다.'),
        ];
    }

    protected function initWithArg()
    {
        $startYear = $this->env['startyear'];

        $this->setDestNation($this->arg['destNationID'], null);

        $this->fullConditionConstraints = [
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ReqEnvValue('year', '>=', $startYear + 1, '초반제한 해제 2년전부터 가능합니다.'),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::NearNation(),
            ConstraintHelper::DisallowDiplomacyBetweenStatus([
                0 => '아국과 이미 교전중입니다.',
                1 => '아국과 이미 선포중입니다.',
                7 => '불가침국입니다.'
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
        return "【{$destNationName}】에 {$commandName}";
    }


    public function run(\Sammo\RandUtil $rng): bool
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

        $josaYi = JosaUtil::pick($generalName, '이');
        $josaYiNation = JosaUtil::pick($nationName, '이');


        $logger = $general->getLogger();
        $destLogger = new ActionLogger(0, $destNationID, $env['year'], $env['month']);

        $logger->pushGeneralActionLog("<D><b>{$destNationName}</b></>에 선전 포고 했습니다.<1>$date</>");
        $logger->pushGeneralHistoryLog("<D><b>{$destNationName}</b></>에 선전 포고");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <D><b>{$destNationName}</b></>에 선전 포고");
        $destLogger->pushNationalHistoryLog("<D><b>{$nationName}</b></>의 <Y>{$generalName}</>{$josaYi} 아국에 선전 포고");

        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <D><b>{$destNationName}</b></>에 <M>선전 포고</> 하였습니다.");
        $logger->pushGlobalHistoryLog("<R><b>【선포】</b></><D><b>{$nationName}</b></>{$josaYiNation} <D><b>{$destNationName}</b></>에 선전 포고 하였습니다.");

        $db->update('diplomacy', [
            'state' => 1,
            'term' => 24
        ], '(me=%i AND you=%i) OR (me=%i AND you=%i)', $nationID, $destNationID, $destNationID, $nationID);

        //국메로 저장
        $text = "【외교】{$env['year']}년 {$env['month']}월:{$nationName}에서 {$destNationName}에 선전포고";

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
        $msg = new Message(
            MessageType::national,
            $src,
            $dest,
            $text,
            new \DateTime($general->getTurnTime()),
            new \DateTime('9999-12-31'),
            []
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
        foreach (getAllNationStaticInfo() as $destNation) {
            $testTurn->setArg(['destNationID' => $destNation['nation']]);
            $testCommand = new static($generalObj, $this->env, $testTurn, ['destNationID' => $destNation['nation']]);

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
            'procRes' => [
                'nationList' => $nationList,
                'startYear' => $this->env['startyear'],
            ],
        ];
    }
}
