<?php

namespace sammo\Command\General;

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
    ScoutMessage
};

use function \sammo\getAllNationStaticInfo;
use function \sammo\getNationStaticInfo;
use function sammo\tryUniqueItemLottery;

use \sammo\Constraint\ConstraintHelper;
use sammo\Enums\GeneralQueryMode;

class che_등용 extends Command\GeneralCommand
{
    static protected $actionName = '등용';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        //NOTE: 사망 직전에 '등용' 턴을 넣을 수 있으므로, 존재하지 않는 장수여도 argTest에서 바로 탈락시키지 않음
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
            'destGeneralID' => $destGeneralID
        ];
        return true;
    }

    protected function init()
    {

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation(['gennum', 'scout']);

        $relYear = $this->env['year'] - $this->env['startyear'];

        $this->permissionConstraints = [
            ConstraintHelper::ReqEnvValue('join_mode', '!=', 'onlyRandom', '랜덤 임관만 가능합니다'),
        ];

        $this->minConditionConstraints = [
            ConstraintHelper::ReqEnvValue('join_mode', '!=', 'onlyRandom', '랜덤 임관만 가능합니다'),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
        ];
    }

    protected function initWithArg()
    {
        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['nation', 'experience', 'dedication'], GeneralQueryMode::Lite);
        $this->setDestGeneral($destGeneral);

        [$reqGold, $reqRice] = $this->getCost();
        $relYear = $this->env['year'] - $this->env['startyear'];

        $this->fullConditionConstraints = [
            ConstraintHelper::ReqEnvValue('join_mode', '!=', 'onlyRandom', '랜덤 임관만 가능합니다'),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::DifferentNationDestGeneral(),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];

        if ($this->destGeneralObj->getVar('officer_level') == 12) {
            $this->fullConditionConstraints[] = ConstraintHelper::AlwaysFail('군주에게는 등용장을 보낼 수 없습니다.');
        }
    }

    public function canDisplay(): bool
    {
        return $this->env['join_mode'] !== 'onlyRandom';
    }

    public function getCost(): array
    {
        $env = $this->env;
        if (!$this->isArgValid) {
            return [$env['develcost'], 0];
        }
        $destGeneral = $this->destGeneralObj;
        $reqGold = Util::round(
            $env['develcost'] +
                ($destGeneral->getVar('experience') + $destGeneral->getVar('dedication')) / 1000
        ) * 10;
        return [$reqGold, 0];
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
        $destGeneralName = $this->destGeneralObj->getName();
        $name = $this->getName();
        $josaUl = JosaUtil::pick($destGeneralName, '을');
        return "【{$destGeneralName}】{$josaUl} {$name}";
    }

    public function run(\Sammo\RandUtil $rng): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $logger = $general->getLogger();

        $destGeneralName = $this->destGeneralObj->getName();
        $destGeneralID = $this->destGeneralObj->getID();


        $msg = ScoutMessage::buildScoutMessage($general->getID(), $destGeneralID, $reason, new \DateTime($general->getTurnTime()));
        if ($msg) {
            $logger->pushGeneralActionLog("<Y>{$destGeneralName}</>에게 등용 권유 서신을 보냈습니다. <1>$date</>");
            $msg->send(true);
        } else {
            $logger->pushGeneralActionLog("<Y>{$destGeneralName}</>에게 등용 권유 서신을 보내지 못했습니다. {$reason} <1>$date</>");
        }

        $exp = 100;
        $ded = 200;

        [$reqGold, $reqRice] = $this->getCost();

        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVar('leadership_exp', 1);
        $general->increaseVarWithLimit('gold', -$reqGold, 0);

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery(\sammo\genGenericUniqueRNGFromGeneral($general), $general);

        $general->applyDB($db);

        return true;
    }

    public function exportJSVars(): array
    {
        $db = DB::db();
        $destRawGenerals = $db->queryAllLists('SELECT no,name,nation,officer_level,npc,leadership,strength,intel FROM general WHERE npc < 2 AND officer_level != 12 AND no != %i ORDER BY npc,binary(name)', $this->generalObj->getID());
        $nationList = [];

        foreach ([getNationStaticInfo(0), ...getAllNationStaticInfo()] as $destNation) {
            $nationList[] = [
                'id' => $destNation['nation'],
                'name' => $destNation['name'],
                'color' => $destNation['color'],
                'power' => $destNation['power'],
            ];
        }

        return [
            'procRes' => [
                'nationList' => $nationList,
                'generals' => $destRawGenerals,
                'generalsKey' => ['no', 'name', 'nationID', 'officerLevel', 'npc', 'leadership', 'strength', 'intel']
            ]
        ];
    }
}
