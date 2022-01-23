<?php

namespace sammo\API\NationBetting;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\DTO\NationBettingInfo;
use sammo\KVStorage;

use function sammo\checkLimit;
use function sammo\increaseRefresh;

class GetBettingList extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
        $db = DB::db();

        increaseRefresh("국가베팅장", 1);

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $nationBettingStor = KVStorage::getStorage($db, 'nation_betting');
        $userID = $session->userID;

        $me = $db->queryFirstRow('SELECT no,nation,officer_level,con,turntime,belong,penalty,permission FROM general WHERE owner=%i', $userID);
        $con = checkLimit($me['con']);
        if ($con >= 2) {
            return "접속 제한중입니다. 1턴 이내에 너무 많은 갱신을 하셨습니다. (다음 갱신 가능 시각 : {$me['turntime']})";
        }

        $bettingList = [];
        foreach ($nationBettingStor->getAll() as $_key => $rawItem) {
            $item = new NationBettingInfo($rawItem);
            $bettingList[$item->id] = $rawItem;
            $bettingList[$item->id]['totalAmount'] = 0;
        }

        $bettingIDList = array_keys($bettingList);
        // XXX: query cache만 믿고 sum을 하는 짓을 벌여도 되는가?
        foreach ($db->queryAllLists(
            'SELECT betting_id, sum(amount) as total_amount FROM ng_betting WHERE betting_id IN %li GROUP BY betting_id',
            $bettingIDList
        ) as [$bettingID, $totalAmount]) {
            if (!key_exists($bettingID, $bettingList)) {
                continue;
            }
            $bettingList[$bettingID]['totalAmount'] = $totalAmount;
        }

        [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);

        return [
            'result' => false,
            'bettingList' => $bettingList,
            'year' => $year,
            'month' => $month,
        ];
    }
}
