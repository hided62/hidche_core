<?php

namespace sammo\API\Nation;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\KVStorage;
use sammo\Validator;
use sammo\WebUtil;

use function sammo\checkSecretPermission;

class SetRate extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'amount',
        ])
            ->rule('int', 'amount')
            ->rule('min', 'amount', 5)
            ->rule('max', 'amount', 30);

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
        $amount = $this->args['amount'];
        $userID = $session->userID;
        $db = DB::db();
        $me = $db->queryFirstRow('SELECT `no`,nation,`officer_level`,permission,penalty FROM general WHERE `owner`=%i', $userID);

        $permission = checkSecretPermission($me, false);
        if($permission < 0){
            return "권한이 부족합니다.";
        }
        if ($me['officer_level'] < 5 && $permission != 4){
            return "권한이 부족합니다.";
        }


        $nationID = $me['nation'];

        $db->update('nation', [
            'rate' => $amount
        ], 'nation=%i', $nationID);

        return [
            'result' => true
        ];
    }
}
