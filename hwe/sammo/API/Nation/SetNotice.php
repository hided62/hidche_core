<?php

namespace sammo\API\Nation;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\KVStorage;
use sammo\TimeUtil;
use sammo\Validator;
use sammo\WebUtil;

use function sammo\checkSecretPermission;

class SetNotice extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'msg',
        ])->rule('lengthMax', 'msg', 16384);

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
        $msg = $this->args['msg'];
        $userID = $session->userID;
        $db = DB::db();
        $me = $db->queryFirstRow('SELECT `no`,`name`,nation,`officer_level`,permission,penalty FROM general WHERE `owner`=%i', $userID);

        $permission = checkSecretPermission($me, false);
        if($permission < 0){
            return "권한이 부족합니다.";
        }
        if ($me['officer_level'] < 5 && $permission != 4){
            return "권한이 부족합니다.";
        }

        $nationID = $me['nation'];

        $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
        $nationStor->nationNotice = [
            'date'=>TimeUtil::now(),
            'msg'=>WebUtil::htmlPurify($msg),
            'author'=>$me['name'],
            'authorID'=>$me['no'],
        ];

        return [
            'result' => true
        ];
    }
}
