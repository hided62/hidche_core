<?php

namespace sammo\API\Betting;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\DTO\BettingInfo;
use sammo\Enums\APIRecoveryType;
use sammo\KVStorage;
use sammo\Validator;

use function sammo\checkLimit;
use function sammo\increaseRefresh;

class GetBettingList extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('in', 'req', ['bettingNation', 'tournament']);
        if (!$v->validate()) {
            return $v->errorStr();
        }
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $db = DB::db();

        $reqType = $this->args['req'] ?? null;

        increaseRefresh("베팅장", 1);

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $bettingStor = KVStorage::getStorage($db, 'betting');
        $userID = $session->userID;

        $me = $db->queryFirstRow(
            'SELECT no,nation,officer_level,refresh_score,turntime,belong,penalty,permission FROM `general`
            LEFT JOIN general_access_log AS l ON `general`.no = l.general_id WHERE owner=%i', $userID
        );
        $limitState = checkLimit($me['refresh_score']);
        if ($limitState >= 2) {
            return "접속 제한중입니다.";
        }

        $bettingList = [];
        foreach ($bettingStor->getAll() as $_key => $rawItem) {
            $item = BettingInfo::fromArray($rawItem);
            if ($reqType !== null && $item->type != $reqType) {
                continue;
            }
            unset($rawItem['candidates']);
            $bettingList[$item->id] = $rawItem;
            $bettingList[$item->id]['totalAmount'] = 0;
        }

        [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);

        if (!$bettingList) {
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
