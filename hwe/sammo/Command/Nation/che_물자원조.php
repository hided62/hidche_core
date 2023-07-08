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
    Json,
    KVStorage,
    StringUtil
};

use function \sammo\getAllNationStaticInfo;
use function \sammo\getNationStaticInfo;


use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_물자원조 extends Command\NationCommand
{
    static protected $actionName = '원조';
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

        if (!key_exists('amountList', $this->arg)) {
            return false;
        }
        $amountList = $this->arg['amountList'];
        if (!is_array($amountList)) {
            return false;
        }

        if (count($amountList) != 2) {
            return false;
        }

        [$goldAmount, $riceAmount] = $amountList;

        if (!is_int($goldAmount) || !is_int($riceAmount)) {
            return false;
        }
        if ($goldAmount < 0 || $riceAmount < 0) {
            return false;
        }
        if ($goldAmount == 0 && $riceAmount == 0) {
            return false;
        }

        $this->arg = [
            'destNationID' => $destNationID,
            'amountList' => [$goldAmount, $riceAmount]
        ];
        return true;
    }

    protected function init()
    {
        $general = $this->generalObj;

        $this->setCity();
        $this->setNation(['gold', 'rice', 'surlimit']);

        $this->minConditionConstraints = [
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ReqNationValue('surlimit', '외교제한', '==', 0, '외교제한중입니다.'),
        ];
    }

    protected function initWithArg()
    {
        $destNationID = $this->arg['destNationID'];
        $this->setDestNation($destNationID, ['gold', 'rice', 'surlimit']);

        [$goldAmount, $riceAmount] = $this->arg['amountList'];
        $limit = $this->nation['level'] * GameConst::$coefAidAmount;

        if ($goldAmount > $limit || $riceAmount > $limit) {
            $this->fullConditionConstraints = [ConstraintHelper::AlwaysFail('작위 제한량 이상은 보낼 수 없습니다.')];
            return;
        }

        $this->fullConditionConstraints = [
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::DifferentDestNation(),
            ConstraintHelper::ReqNationGold(GameConst::$basegold + (($goldAmount > 0) ? 1 : 0)),
            ConstraintHelper::ReqNationRice(GameConst::$baserice + (($riceAmount > 0) ? 1 : 0)),
            ConstraintHelper::ReqNationValue('surlimit', '외교제한', '==', 0, '외교제한중입니다.'),
            ConstraintHelper::ReqDestNationValue('surlimit', '외교제한', '==', 0, '상대국이 외교제한중입니다.'),
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
        //NOTE: 자체 postReqTurn 사용
        return 12;
    }

    public function getNextAvailableTurn(): ?int
    {
        return null;
    }

    public function setNextAvailable(?int $yearMonth = null)
    {
        return;
    }

    public function getBrief(): string
    {
        [$goldAmount, $riceAmount] = $this->arg['amountList'];
        $goldAmountText = number_format($goldAmount);
        $riceAmountText = number_format($riceAmount);
        $destNationName = $this->destNation['name'];
        $commandName = $this->getName();
        return "【{$destNationName}】에게 국고 {$goldAmountText} 병량 {$riceAmountText} {$commandName}";
    }


    public function run(\Sammo\RandUtil $rng): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $generalID = $general->getID();
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $nation = $this->nation;
        $nationID = $nation['nation'];
        $nationName = $nation['name'];

        $destNationID = $this->destNation['nation'];
        $destNationName = $this->destNation['name'];


        [$goldAmount, $riceAmount] = $this->arg['amountList'];


        $goldAmount = Util::valueFit(
            $goldAmount,
            0,
            $nation['gold'] - GameConst::$basegold
        );

        $riceAmount = Util::valueFit(
            $riceAmount,
            0,
            $nation['rice'] - GameConst::$baserice
        );

        $goldAmountText = number_format($goldAmount);
        $riceAmountText = number_format($riceAmount);


        $logger = $general->getLogger();

        $year = $this->env['year'];
        $month = $this->env['month'];



        $josaRo = JosaUtil::pick($destNationName, '로');



        $broadcastMessage = "<D><b>{$destNationName}</b></>{$josaRo} 금<C>{$goldAmountText}</> 쌀<C>{$riceAmountText}</>을 지원했습니다.";

        $chiefList = $db->queryFirstColumn('SELECT no FROM general WHERE officer_level >= 5 AND no != %i AND nation = %i', $generalID, $nationID);
        foreach ($chiefList as $chiefID) {
            $chiefLogger = new ActionLogger($chiefID, $nationID, $year, $month);
            $chiefLogger->pushGeneralActionLog($broadcastMessage, ActionLogger::PLAIN);
            $chiefLogger->flush();
        }

        $josaUlRiceAmount = JosaUtil::pick($riceAmountText, '을');

        $logger->pushGeneralHistoryLog("<D><b>{$destNationName}</b></>{$josaRo} 금<C>{$goldAmountText}</> 쌀<C>{$riceAmountText}</>{$josaUlRiceAmount} 지원");
        $logger->pushNationalHistoryLog("<D><b>{$destNationName}</b></>{$josaRo} 금<C>{$goldAmountText}</> 쌀<C>{$riceAmountText}</>{$josaUlRiceAmount} 지원");
        $logger->pushGlobalHistoryLog("<Y><b>【원조】</b></><D><b>{$nationName}</b></>에서 <D><b>{$destNationName}</b></>{$josaRo} 물자를 지원합니다");

        $logger->pushGeneralActionLog($broadcastMessage);
        $logger->pushGeneralActionLog("<D><b>{$destNationName}</b></>{$josaRo} 물자를 지원합니다. <1>$date</>", ActionLogger::PLAIN);

        $destBroadcastMessage = $broadcastMessage = "<D><b>{$nationName}</b></>에서 금<C>{$goldAmountText}</> 쌀<C>{$riceAmountText}</>{$josaUlRiceAmount} 원조했습니다.";
        $destChiefList = $db->queryFirstColumn('SELECT no FROM general WHERE officer_level >= 5 AND nation = %i', $destNationID);
        foreach ($destChiefList as $destChiefID) {
            $destChiefLogger = new ActionLogger($destChiefID, $nationID, $year, $month);
            $destChiefLogger->pushGeneralActionLog($destBroadcastMessage, ActionLogger::PLAIN);
            $destChiefLogger->flush();
        }

        $josaRoSrc = JosaUtil::pick($nationName, '로');
        $destNationLogger = new ActionLogger(0, $destNationID, $year, $month);
        $destNationLogger->pushNationalHistoryLog("<D><b>{$nationName}</b></>{$josaRoSrc}부터 금<C>{$goldAmountText}</> 쌀<C>{$riceAmountText}</>{$josaUlRiceAmount} 지원 받음");

        $destNationStor = KVStorage::getStorage(DB::db(), $destNationID, 'nation_env');
        $destRecvAssist = $destNationStor->getValue('recv_assist') ?? [];
        $destRecvAssist["n{$nationID}"] = [$nationID, ($destRecvAssist["n{$nationID}"][1] ?? 0) + $goldAmount + $riceAmount];
        $destNationStor->setValue('recv_assist', $destRecvAssist);

        $db->update('nation', [
            'gold' => $db->sqleval('gold - %i', $goldAmount),
            'rice' => $db->sqleval('rice - %i', $riceAmount),
            'surlimit' => $db->sqleval('surlimit + %i', $this->getPostReqTurn())
        ], 'nation = %i', $nationID);

        $db->update('nation', [
            'gold' => $db->sqleval('gold + %i', $goldAmount),
            'rice' => $db->sqleval('rice + %i', $riceAmount),
        ], 'nation = %i', $destNationID);

        $general->addExperience(5);
        $general->addDedication(5);

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);

        return true;
    }

    public function exportJSVars(): array
    {
        $generalObj = $this->generalObj;
        $nationList = [];
        foreach (getAllNationStaticInfo() as $destNation) {
            $nationTarget = [
                'id' => $destNation['nation'],
                'name' => $destNation['name'],
                'color' => $destNation['color'],
                'power' => $destNation['power'],
            ];

            //TODO: 물자원조 자체가 가능한지도 검사?

            if ($nationTarget['id'] == $generalObj->getNationID()) {
                $nationTarget['notAvailable'] = true;
            }

            $nationList[] = $nationTarget;
        }
        $currentNationLevel = getNationStaticInfo($this->generalObj->getNationID())['level'];

        $levelInfo = [];
        foreach (\sammo\getNationLevelList() as $level => [$levelText,,]) {
            $levelInfo[$level] = ['text' => $levelText, 'amount' => $level * GameConst::$coefAidAmount];
        }

        $amountGuide = [];
        foreach (Util::range(1, $currentNationLevel + 1) as $nationLevel) {
            $amountGuide[] = $nationLevel * GameConst::$coefAidAmount;
        }

        return [
            'procRes' => [
                'nationList' => $nationList,
                'currentNationLevel' => $currentNationLevel,
                'levelInfo' => $levelInfo,
                'minAmount' => 1000,
                'maxAmount' => Util::array_last($amountGuide),
                'amountGuide' => $amountGuide,
            ],
        ];
    }
}
