<?php

namespace sammo\API\Admin;

use sammo\Session;
use DateTimeInterface;
use sammo\BaseAPI;
use sammo\Enums\APIRecoveryType;
use sammo\RootDB;
use sammo\TimeUtil;
use sammo\Validator;

class BanEmailAddress extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v
            ->rule('required', [
                'email',
            ]);

        if (!$v->validate()) {
            return $v->errorStr();
        }
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return BaseAPI::REQ_LOGIN | BaseAPI::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $RootDB = RootDB::db();
        $email = $this->args['email'];

        if ($session->userGrade < 5) {
            return '권한이 없습니다.';
        }
        $globalSalt = RootDB::getGlobalSalt();

        try{
            $RootDB->insert('banned_member', [
                'hashed_email' => hash('sha512', $globalSalt.$email.$globalSalt),
                'info' => TimeUtil::now(),
            ]);
        }
        catch(\Exception $e){
            return '이미 등록된 이메일입니다.';
        }

        return [
            'result' => true,
            'reason' => '등록되었습니다.'
        ];
    }
}
