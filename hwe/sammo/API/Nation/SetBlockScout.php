<?php

namespace sammo\API\Nation;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\KVStorage;
use sammo\Validator;
use sammo\WebUtil;

use function sammo\checkSecretPermission;

class SetBlockScout extends \sammo\BaseAPI
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

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
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

        $gameStor = new KVStorage($db, 'game_env');
        $blockChangeScout = $gameStor->getValue('block_change_scout')??false;
        if ($blockChangeScout){
            return "임관 설정을 바꿀 수 없도록 설정되어 있습니다.";
        }

        $nationID = $me['nation'];

        $db->update('nation', [
            'scout' => $value?1:0,
        ], 'nation=%i', $nationID);

        return [
            'result' => true
        ];
    }
}
