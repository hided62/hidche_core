<?php

namespace sammo\API\Command;

use sammo\Session;
use DateTimeInterface;
use sammo\Command\UserActionCommand;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Json;
use sammo\KVStorage;
use sammo\TimeUtil;
use sammo\DTO\UserAction;

use function sammo\cutTurn;

class GetReservedUserAction extends \sammo\BaseAPI
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
        $generalID = $session->generalID;

        $userActionKey = UserActionCommand::USER_ACTION_KEY;

        $rawUserActions = $db->queryFirstField('SELECT JSON_QUERY(`aux`, %s) FROM general WHERE no=%i', "$.{$userActionKey}", $generalID);
        if($rawUserActions === null){
            $rawUserActions = '{}';
        }

        $userActions = UserAction::fromArray(Json::decode($rawUserActions));

        return [
            'result' => true,
            'userActions' => $userActions->toArray(),
        ];
    }
}
