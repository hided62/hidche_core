<?php

namespace sammo\Command\General;

use \sammo\{
    DB,
    Util,
    JosaUtil,
    General,
    ActionLogger,
    GameConst,
    GameUnitConst,
    LastTurn,
    Command,
    Json,
    KVStorage
};

use function \sammo\tryUniqueItemLottery;
use function \sammo\getInvitationList;
use function \sammo\getNationStaticInfo;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;



class che_임관 extends Command\GeneralCommand
{
    static protected $actionName = '임관';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        $destNationID = $this->arg['destNationID'] ?? null;

        if ($destNationID === null) {
            return false;
        }

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

        $relYear = $env['year'] - $env['startyear'];

        $this->permissionConstraints = [
            ConstraintHelper::ReqEnvValue('join_mode', '!=', 'onlyRandom', '랜덤 임관만 가능합니다')
        ];

        $this->minConditionConstraints = [
            ConstraintHelper::ReqEnvValue('join_mode', '!=', 'onlyRandom', '랜덤 임관만 가능합니다'),
            ConstraintHelper::BeNeutral(),
            ConstraintHelper::AllowJoinAction()
        ];
    }

    public function getCommandDetailTitle(): string
    {
        return '지정한 국가로 임관';
    }

    public function canDisplay(): bool
    {
        return $this->env['join_mode'] !== 'onlyRandom';
    }

    protected function initWithArg()
    {
        $destNationID = $this->arg['destNationID'];
        $this->setDestNation($destNationID, ['gennum', 'scout']);

        $env = $this->env;
        $relYear = $env['year'] - $env['startyear'];
        $this->fullConditionConstraints = [
            ConstraintHelper::ReqEnvValue('join_mode', '!=', 'onlyRandom', '랜덤 임관만 가능합니다'),
            ConstraintHelper::BeNeutral(),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::AllowJoinDestNation($relYear),
            ConstraintHelper::AllowJoinAction()
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
        $josaRo = JosaUtil::pick($destNationName, '로');
        return "【{$destNationName}】{$josaRo} {$commandName}";
    }

    public function run(): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);
        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $destNation = $this->destNation;
        $gennum = $destNation['gennum'];
        $destNationID = $destNation['nation'];
        $destNationName = $destNation['name'];

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("<D>{$destNationName}</>에 임관했습니다. <1>$date</>");
        $logger->pushGeneralHistoryLog("<D><b>{$destNationName}</b></>에 임관");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <D><b>{$destNationName}</b></>에 <S>임관</>했습니다.");

        if ($gennum < GameConst::$initialNationGenLimit) {
            $exp = 700;
        } else {
            $exp = 100;
        }

        $general->setVar('nation', $destNationID);
        $general->setVar('officer_level', 1);
        $general->setVar('officer_city', 0);
        $general->setVar('belong', 1);

        if ($this->destGeneralObj !== null) {
            $general->setVar('city', $this->destGeneralObj->getCityID());
        } else {
            $targetCityID = $db->queryFirstField('SELECT city FROM general WHERE nation = %i AND officer_level=12', $destNationID);
            $general->setVar('city', $targetCityID);
        }

        $db->update('nation', [
            'gennum' => $db->sqleval('gennum + 1')
        ], 'nation=%i', $destNationID);
        \sammo\refreshNationStaticInfo();

        $relYear = $env['year'] - $env['startyear'];
        if ($general->getNPCType() == 1 || $relYear >= 3) {
            $joinedNations = $general->getAuxVar('joinedNations') ?? [];
            $joinedNations[] = $destNationID;
            $general->setAuxVar('joinedNations', $joinedNations);
        }

        $general->increaseInheritancePoint('active_action', 1);
        $general->addExperience($exp);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    public function exportJSVars(): array
    {
        $generalObj = $this->generalObj;
        $nationID = $generalObj->getNationID();
        $db = DB::db();

        $rawNationList = Util::convertArrayToDict($db->query('SELECT nation,`name`,color,gennum,`power` FROM nation'), 'nation');
        $scoutMsgs = KVStorage::getValuesFromInterNamespace($db, 'nation_env', 'scout_msg');
        foreach ($scoutMsgs as $nationID => $scoutMsg) {
            $rawNationList[$nationID]['scoutmsg'] = $scoutMsg;
        }
        foreach ($rawNationList as $destNation) {
            $testCommand = new static($generalObj, $this->env, ['destNationID' => $destNation['nation']]);

            $nationTarget = [
                'id' => $destNation['nation'],
                'name' => $destNation['name'],
                'color' => $destNation['color'],
                'power' => $destNation['power'],
                'scoutMsg' => $destNation['scoutmsg'] ?? ' ',
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
            ],
        ];
    }
}
