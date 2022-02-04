<?php

namespace sammo;

use sammo\DTO\BettingInfo;

class Betting
{

    private BettingInfo $info;

    public function __construct(private $bettingID)
    {
        $db = DB::db();
        $bettingStor = KVStorage::getStorage($db, 'betting');
        $rawBettingInfo = $bettingStor->getValue("id_{$bettingID}");
        if ($rawBettingInfo === null) {
            throw new \RuntimeException("해당 베팅이 없습니다: {$bettingID}");
        }
        $this->info = new BettingInfo($rawBettingInfo);
    }

    private function _convertBettingKey(array $bettingType): string
    {
        return Json::encode($bettingType);
    }

    public function purifyBettingKey(array $bettingType): array
    {
        $selectCnt = $this->info->selectCnt;
        sort($bettingType, SORT_NUMERIC);
        $bettingType = array_unique($bettingType, SORT_NUMERIC); //NOTE: key로 바로 사용하므로 중요함
        if (count($bettingType) != $selectCnt) {
            throw new \InvalidArgumentException('중복된 값이 있습니다.');
        }

        if ($bettingType[0] < 0) {
            throw new \InvalidArgumentException('올바르지 않은 값이 있습니다.(0 미만)' . print_r($bettingType, true));
        }

        if (Util::array_last($bettingType) >= count($this->info->candidates)) {
            throw new \InvalidArgumentException('올바르지 않은 값이 있습니다.(초과)' . print_r($bettingType, true));
        }

        return $bettingType;
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

    /** @param int[] $result */
    private function _calcRewardExclusive(array $bettingType): array
    {
        $db = DB::db();
        $totalAmount = $db->queryFirstField('SELECT sum(amount) FROM ng_betting WHERE betting_id = %i');

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

        if ($this->info->isExlusive) {
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
        $rewardAmount = [];
        foreach (Util::range($selectCnt, 0, -1) as $matchPoint) {
            if (count($subWinners[$matchPoint]) == 0) {
                continue;
            }
            if ($subAmount[$matchPoint] == 0) {
                continue;
            }

            $givenRewardAmount = $remainRewardAmount / 2;
            $rewardAmount[$matchPoint] = $givenRewardAmount;
            $remainRewardAmount -= $givenRewardAmount; // /2가 아니라 다른 값이 될 경우를 대비..
        }

        foreach (Util::range(1, $selectCnt + 1) as $matchPoint) {
            if (!key_exists($matchPoint, $rewardAmount)) {
                continue;
            }
            $rewardAmount[$matchPoint] += $remainRewardAmount;
            break;
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

        if ($this->info->reqInheritancePoint) {
            /** @var UserLogger[] */
            $loggers = [];
            foreach ($rewardList as $rewardItem) {
                if ($rewardItem['userID'] === null) {
                    continue;
                }
                $userID = $rewardItem['userID'];
                $amount = $rewardItem['amount'];

                $inheritStor = KVStorage::getStorage($db, "inheritance_{$userID}");
                $previousPoint = ($inheritStor->getValue('previous') ?? [0, 0])[0];
                $nextPoint = $previousPoint + $amount;
                $inheritStor->setValue('previous', [$nextPoint, 0]);
                $inheritStor->invalidateCacheValue('previous');//XXX: 실제로는 previous 값을 사용할 수 없도록 락을 걸어야 한다.

                $amountText = number_format($amount);
                $previousPointText = number_format($previousPoint);
                $nextPointText = number_format($nextPoint);

                $matchPoint = $rewardItem['matchPoint'];

                if ($matchPoint == $selectCnt) {
                    $partialText = '베팅 당첨';
                } else {
                    $partialText = "베팅 부분 당첨({$matchPoint}/{$selectCnt})";
                }

                if (key_exists($userID, $loggers)) {
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
            $generalList = General::createGeneralObjListFromDB(array_unique(Util::squeezeFromArray($rewardList, 'generalID')), ['gold', 'npc'], 1);
            foreach ($rewardList as $rewardItem) {
                $gambler = $generalList[$rewardItem['generalID']];
                $reward = Util::round($rewardItem['amount']);
                $matchPoint = $rewardItem['matchPoint'];
                $gambler->increaseVar('gold', $reward);
                if (($gambler->getNPCType() == 0) || ($gambler->getNPCType() == 1 && $gambler->getRankVar('betgold', 0) > 0)) {
                    $gambler->increaseRankVar('betwingold', $reward);
                    $gambler->increaseRankVar('betwin', 1);
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
