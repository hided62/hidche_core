<?php

namespace sammo;

use Ds\Map;
use sammo\DTO\BettingInfo;
use sammo\DTO\BettingItem;
use sammo\Enums\GeneralQueryMode;
use sammo\Enums\InheritanceKey;
use sammo\Enums\RankColumn;

class Betting
{

    private BettingInfo $info;

    public const LAST_BETTING_ID_KEY = 'last_betting_id';

    static public function genNextBettingID(): int
    {
        $db = DB::db();

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $gameStor->invalidateCacheValue(self::LAST_BETTING_ID_KEY);
        $bettingID = ($gameStor->getValue(self::LAST_BETTING_ID_KEY) ?? 0) + 1;

        $gameStor->setValue(self::LAST_BETTING_ID_KEY, $bettingID);

        return $bettingID;
    }

    static public function openBetting(BettingInfo $info)
    {
        $db = DB::db();
        $bettingID = $info->id;
        $bettingStor = KVStorage::getStorage($db, 'betting');
        $bettingStor->setValue("id_{$bettingID}", $info->toArray());
    }

    public function __construct(private $bettingID)
    {
        $db = DB::db();
        $bettingStor = KVStorage::getStorage($db, 'betting');
        $rawBettingInfo = $bettingStor->getValue("id_{$bettingID}");
        if ($rawBettingInfo === null) {
            throw new \RuntimeException("해당 베팅이 없습니다: {$bettingID}");
        }
        $this->info = BettingInfo::fromArray($rawBettingInfo);
    }

    private function _convertBettingKey(array $bettingType): string
    {
        return Json::encode($bettingType);
    }

    public function purifyBettingKey(array $bettingType, bool $noValidate = false): array
    {
        $selectCnt = $this->info->selectCnt;
        sort($bettingType, SORT_NUMERIC);
        $bettingType = array_unique($bettingType, SORT_NUMERIC); //NOTE: key로 바로 사용하므로 중요함
        if (count($bettingType) != $selectCnt) {
            throw new \InvalidArgumentException('중복된 값이 있습니다.');
        }

        if (!$noValidate) {
            foreach($bettingType as $bettingKey){
                if(!key_exists($bettingKey, $this->info->candidates)){
                    throw new \InvalidArgumentException('올바른 후보가 아닙니다.' . print_r($bettingType, true));
                }
            }
        }

        return $bettingType;
    }

    public function closeBetting(): void
    {
        $db = DB::db();
        $bettingStor = KVStorage::getStorage($db, 'betting');
        $bettingID = $this->info->id;
        $gameStor = KVStorage::getStorage($db, 'game_env');

        //XXX: 베팅 종료 시점을 '현재 연월'로 하여 강제로 닫는다.
        [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
        $this->info->closeYearMonth = Util::joinYearMonth($year, $month);
        $bettingStor->setValue("id_{$bettingID}", $this->info->toArray());
    }

    public function convertBettingKey(array $bettingType): string
    {
        $bettingType = $this->purifyBettingKey($bettingType);
        return $this->_convertBettingKey($bettingType);
    }

    public function getInfo(): BettingInfo
    {
        return $this->info;
    }

    public function bet(int $generalID, ?int $userID, array $bettingType, int $amount): void
    {
        $bettingInfo = $this->info;

        if ($bettingInfo->finished) {
            throw new \RuntimeException('이미 종료된 베팅입니다');
        }

        if ($bettingInfo->finished) {
            throw new \RuntimeException('이미 종료된 베팅입니다');
        }
        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');

        [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
        $yearMonth = Util::joinYearMonth($year, $month);


        if ($bettingInfo->closeYearMonth <= $yearMonth) {
            throw new \RuntimeException('이미 마감된 베팅입니다');
        }

        if ($bettingInfo->openYearMonth > $yearMonth) {
            throw new \RuntimeException('아직 시작되지 않은 베팅입니다');
        }

        if (count($bettingType) != $bettingInfo->selectCnt) {
            throw new \RuntimeException('필요한 선택 수를 채우지 못했습니다.');
        }

        $resKey = $this->info->reqInheritancePoint?'유산포인트':'금';
        $bettingTypeKey = $this->convertBettingKey($bettingType);

        $inheritStor = KVStorage::getStorage($db, "inheritance_{$userID}");

        $prevBetAmount = $db->queryFirstField('SELECT sum(amount) FROM ng_betting WHERE betting_id = %i AND user_id = %i', $this->bettingID, $userID) ?? 0;

        if ($prevBetAmount + $amount > 1000) {
            throw new \RuntimeException((1000 - $prevBetAmount) . $resKey.'까지만 베팅 가능합니다.');
        }

        if ($bettingInfo->reqInheritancePoint) {
            $remainPoint = ($inheritStor->getValue('previous') ?? [0, 0])[0];
            if ($remainPoint < $amount) {
                throw new \RuntimeException('유산포인트가 충분하지 않습니다.');
            }
        } else {
            $remainPoint = $db->queryFirstField('SELECT gold FROM general WHERE no = %i', $generalID) ?? 0;
            if ($remainPoint < GameConst::$minGoldRequiredWhenBetting + $amount) {
                throw new \RuntimeException('금이 부족합니다.');
            }
        }

        $bettingItem = new BettingItem(
            rowID: null,
            bettingID: $this->bettingID,
            generalID: $generalID,
            userID: $userID,
            bettingType: $bettingTypeKey,
            amount: $amount
        );

        $db->insertUpdate(
            'ng_betting',
            $bettingItem->toArray(),
            ['amount' => $db->sqleval('amount + %i', $amount)]
        );
        if ($bettingInfo->reqInheritancePoint) {
            $inheritStor->setValue('previous', [$remainPoint - $amount, null]);
            $userLogger = new UserLogger($userID);
            $userLogger->push("{$amount} 포인트를 베팅에 사용", "inheritPoint");
            $userLogger->flush();
            $db->update('rank_data', [
                'value'=>$db->sqleval('value + %i', $amount)
            ], 'general_id = %i AND type = %s', $generalID, RankColumn::inherit_point_spent_dynamic->value);
        } else {
            $db->update('general', [
                'gold' => $db->sqleval('gold - %i', $amount)
            ], 'no = %i', $generalID);
            $db->update('rank_data', [
                'value'=>$db->sqleval('value + %i', $amount)
            ], 'general_id = %i AND type = "betgold"', $generalID);
        }
    }

    /** @param int[] $result */
    private function _calcRewardExclusive(array $bettingType): array
    {
        $db = DB::db();
        $totalAmount = $db->queryFirstField('SELECT sum(amount) FROM ng_betting WHERE betting_id = %i', $this->bettingID);

        if ($totalAmount == 0) {
            return [];
        }

        $winnerList = $db->queryAllLists(
            'SELECT general_id, user_id, amount FROM ng_betting WHERE betting_id = %i AND betting_type = %s AND general_id > 0',
            $this->bettingID,
            $this->_convertBettingKey($bettingType)
        );

        $subAmount = 0;
        foreach ($winnerList as [,, $amount]) {
            $subAmount += $amount;
        }

        if ($subAmount == 0) {
            $refundList = [];
            //당첨자가 아무도 없다면 무효로 하자.
            foreach ($db->queryAllLists(
                'SELECT general_id, user_id, amount, betting_type FROM ng_betting WHERE betting_id = %i',
                $this->bettingID
            ) as [$generalID, $userID, $amount, $bettingTypeKey]) {
                $refundList[] = [
                    'generalID' => $generalID,
                    'userID' => $userID,
                    'amount' => $amount,
                    'matchPoint' => 0,
                ];
            }
            return [];
        }

        $multiplier = $totalAmount / $subAmount;
        $selectCnt = $this->info->selectCnt;

        $result = [];
        foreach ($winnerList as [$generalID, $userID, $amount]) {
            $result[] = [
                'generalID' => $generalID,
                'userID' => $userID,
                'amount' => $amount * $multiplier,
                'matchPoint' => $selectCnt,
            ];
        }
        return $result;
    }



    /** @param int[] $winnerType */
    public function calcReward(array $winnerType): array
    {
        $selectCnt = $this->info->selectCnt;
        if ($selectCnt == 1) {
            return $this->_calcRewardExclusive($winnerType);
        }

        if ($this->info->isExclusive) {
            return $this->_calcRewardExclusive($winnerType);
        }
        //아래는 2개 이상, 복합 보상 옵션

        $winnerTypeMap = [];
        foreach ($winnerType as $typeVal) {
            $winnerTypeMap[$typeVal] = $typeVal;
        }

        $calcMatchPoint = function ($bettingType) use ($winnerTypeMap): int {
            $result = 0;
            foreach ($bettingType as $typeVal) {
                if (key_exists($typeVal, $winnerTypeMap)) {
                    $result += 1;
                }
            }
            return $result;
        };

        $totalAmount = 0;
        $subAmount = [];
        $subWinners = [];

        foreach (Util::range($selectCnt + 1) as $matchPoint) {
            $subAmount[$matchPoint] = 0;
            $subWinners[$matchPoint] = [];
        }

        $db = DB::db();
        foreach ($db->queryAllLists(
            'SELECT general_id, user_id, amount, betting_type FROM ng_betting WHERE betting_id = %i',
            $this->bettingID
        ) as [$generalID, $userID, $amount, $bettingTypeKey]) {
            $bettingType = Json::decode($bettingTypeKey);
            $matchPoint = $calcMatchPoint($bettingType);
            $totalAmount += $amount;
            if ($generalID == 0) {
                continue;
            }
            $subAmount[$matchPoint] += $amount;
            $subWinners[$matchPoint][] = [
                'generalID' => $generalID,
                'userID' => $userID,
                'amount' => $amount,
                'matchPoint' => $matchPoint,
            ];
        }

        $remainRewardAmount = $totalAmount;
        $accumulatedRewardAmount = 0;
        $givenRewardAmount = $totalAmount;
        $rewardAmount = [];
        foreach (Util::range($selectCnt, 0, -1) as $matchPoint) {
            $givenRewardAmount /= 2;
            $accumulatedRewardAmount += $givenRewardAmount;
            if (count($subWinners[$matchPoint]) == 0) {
                continue;
            }
            if ($subAmount[$matchPoint] == 0) {
                continue;
            }

            $rewardAmount[$matchPoint] = $accumulatedRewardAmount;
            $remainRewardAmount -= $accumulatedRewardAmount;
            $accumulatedRewardAmount = 0;
        }

        //남은 상금은 '당첨자'에게 몰아준다.
        //당첨자가 아무도 없다면, 0개 맞춘 그룹에게 돌아간다.
        if ($rewardAmount) {
            foreach (Util::range($selectCnt, -1, -1) as $matchPoint) {
                if (!key_exists($matchPoint, $rewardAmount)) {
                    continue;
                }
                $rewardAmount[$matchPoint] += $remainRewardAmount;
                break;
            }
        }

        $result = [];

        foreach (Util::range($selectCnt + 1, 0, -1) as $matchPoint) {
            if (!key_exists($matchPoint, $rewardAmount)) {
                continue;
            }
            $subReward = $rewardAmount[$matchPoint];
            if ($subReward == 0) {
                continue;
            }
            $multiplier = $subReward / $subAmount[$matchPoint];
            foreach ($subWinners[$matchPoint] as $subWinner) {
                $subWinner['amount'] *= $multiplier;
                $result[] = $subWinner;
            }
        }

        return $result;
    }

    public function giveReward(array $winnerType)
    {
        $rewardList = $this->calcReward($winnerType);
        $selectCnt = $this->info->selectCnt;

        $db = DB::db();

        $pointManager = InheritancePointManager::getInstance();

        if ($this->info->reqInheritancePoint) {
            /** @var Map<int,UserLogger> */
            $loggers = new Map();
            foreach ($rewardList as $rewardItem) {
                if ($rewardItem['userID'] === null) {
                    continue;
                }
                /** @var int */
                $userID = $rewardItem['userID'];
                $amount = $rewardItem['amount'];

                $nextPoint = $pointManager->increaseInheritancePointRaw($userID, InheritanceKey::previous, $amount);
                $previousPoint = $nextPoint - $amount;//역산

                $generalID = $rewardItem['generalID'];
                $db->update('rank_data', [
                    'value'=>$db->sqleval('value + %i', $amount)
                ], 'general_id = %i AND type = %s', $generalID, RankColumn::inherit_point_earned_by_action->value);

                $amountText = number_format($amount);
                $previousPointText = number_format($previousPoint);
                $nextPointText = number_format($nextPoint);

                $matchPoint = $rewardItem['matchPoint'];

                if ($matchPoint == $selectCnt) {
                    $partialText = '베팅 당첨';
                } else {
                    $partialText = "베팅 부분 당첨({$matchPoint}/{$selectCnt})";
                }

                if ($loggers->hasKey($userID)) {
                    $userLogger = $loggers[$userID];
                } else {
                    $userLogger = new UserLogger($userID);
                    $loggers[$userID] = $userLogger;
                }

                [$year, $month] = Util::parseYearMonth($this->info->openYearMonth);

                $userLogger->push("{$this->info->name} {$partialText} 보상으로 {$amountText} 포인트 획득.",  "inheritPoint");
                $userLogger->push("포인트 {$previousPointText} => {$nextPointText}", "inheritPoint");
            }

            foreach ($loggers as $userLogger) {
                $userLogger->flush();
            }
        } else {
            $generalList = General::createObjListFromDB(array_unique(Util::squeezeFromArray($rewardList, 'generalID')));
            foreach ($rewardList as $rewardItem) {
                $gambler = $generalList[$rewardItem['generalID']];
                $reward = Util::round($rewardItem['amount']);
                $matchPoint = $rewardItem['matchPoint'];
                $gambler->increaseVar('gold', $reward);
                if (($gambler->getNPCType() == 0) || ($gambler->getNPCType() == 1 && $gambler->getRankVar(RankColumn::betgold, 0) > 0)) {
                    $gambler->increaseRankVar(RankColumn::betwingold, $reward);
                    $gambler->increaseRankVar(RankColumn::betwin, 1);
                }

                if ($matchPoint == $selectCnt) {
                    $partialText = '베팅 당첨';
                } else {
                    $partialText = "베팅 부분 당첨({$matchPoint}/{$selectCnt})";
                }
                $rewardText = number_format($reward);
                $gambler->getLogger()->pushGeneralActionLog("<C>{$this->info->name}</>의 {$partialText} 보상으로 <C>{$rewardText}</>의 <S>금</> 획득!", ActionLogger::EVENT_PLAIN);
            }

            foreach ($generalList as $general) {
                $general->applyDB($db);
            }
        }

        $this->info->finished = true;
        $this->info->winner = $winnerType;
        $bettingStor = KVStorage::getStorage($db, 'betting');
        $bettingStor->setValue("id_{$this->bettingID}", $this->info->toArray());
    }
}
