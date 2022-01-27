<?php

namespace sammo\API\Betting;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\DTO\BettingInfo;
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

        increaseRefresh("베팅장", 1);

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $bettingStor = KVStorage::getStorage($db, 'betting');
        $userID = $session->userID;

        $me = $db->queryFirstRow('SELECT no,nation,officer_level,con,turntime,belong,penalty,permission FROM general WHERE owner=%i', $userID);
        $con = checkLimit($me['con']);
        if ($con >= 2) {
            return "접속 제한중입니다.";
        }

        $bettingList = [];
        foreach ($bettingStor->getAll() as $_key => $rawItem) {
            $item = new BettingInfo($rawItem);
            unset($rawItem['candidates']);
            $bettingList[$item->id] = $rawItem;
            $bettingList[$item->id]['totalAmount'] = 0;
        }

        [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);

        if(!$bettingList){
            return [
                'result' => true,
                'bettingList' => $bettingList,
                'year' => $year,
                'month' => $month,
            ];
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


        return [
            'result' => true,
            'bettingList' => $bettingList,
            'year' => $year,
            'month' => $month,
        ];
    }
}
