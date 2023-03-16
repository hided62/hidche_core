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

class SetBlockWar extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'value',
        ])
            ->rule('boolean', 'value');

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
        $value = $this->args['value'];
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
        $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');


        $avilableCnt = $nationStor->getValue('available_war_setting_cnt') ?? 0;
        if ($avilableCnt <= 0){
            return "잔여 횟수가 부족합니다.";
        }


        $db->update('nation', [
            'war' => $value?1:0,
        ], 'nation=%i', $nationID);
        $nationStor->setValue('available_war_setting_cnt', $avilableCnt - 1);

        return [
            'result' => true,
            'availableCnt' => $avilableCnt - 1,
        ];
    }
}
