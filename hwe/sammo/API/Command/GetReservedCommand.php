<?php

namespace sammo\API\Command;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Json;
use sammo\KVStorage;
use sammo\TimeUtil;

use function sammo\cutTurn;

class GetReservedCommand extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $db = DB::db();

        $commandList = [];
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $generalID = $session->generalID;

        $rawTurn = $db->queryAllLists('SELECT turn_idx, action, arg, brief FROM general_turn WHERE general_id = %i ORDER BY turn_idx ASC', $generalID);
        foreach ($rawTurn as [$turn_idx, $action, $arg, $brief]) {
            $commandList[$turn_idx] = [
                'action' => $action,
                'brief' => $brief,
                'arg' => Json::decode($arg)
            ];
        }


        [$turnTerm, $year, $month, $lastExecute] = $gameStor->getValuesAsArray(['turnterm', 'year', 'month', 'turntime']);

        [$turnTime, $rawGeneralAux] = $db->queryFirstList('SELECT turntime, aux FROM general WHERE no=%i', $generalID);
        $generalAux = Json::decode($rawGeneralAux ?? '{}');

        if (cutTurn($turnTime, $turnTerm) > cutTurn($lastExecute, $turnTerm)) {
            //이미 이번달에 실행된 턴이다.
            $month++;
            if ($month >= 13) {
                $month -= 12;
                $year += 1;
            }
        }

        return [
            'result' => true,
            'turnTime' => $turnTime,
            'turnTerm' => $turnTerm,
            'year' => $year,
            'month' => $month,
            'date' => TimeUtil::now(true),
            'turn' => $commandList,
            'autorun_limit' => $generalAux['autorun_limit'] ?? null,
        ];
    }
}
