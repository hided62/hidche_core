<?php

namespace sammo\API\NationCommand;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Validator;

use function sammo\pushNationCommand;

class PushCommand extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'amount',
        ])
            ->rule('int', 'amount')
            ->rule('min', 'amount', -12)
            ->rule('max', 'amount', 12);

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
        if($amount == 0){
            return '0은 불가능합니다';
        }
        $db = DB::db();
        $me = $db->queryFirstRow('SELECT officer_level, nation FROM general WHERE no = %i', $session->generalID);
        if(!$me){
            return '올바르지 않은 장수입니다.';
        }

        if(!$me['nation']){
            return '국가에 소속되어 있지 않습니다.';
        }

        if($me['officer_level'] < 5){
            return '수뇌가 아닙니다.';
        }

        pushNationCommand($me['nation'],  $me['officer_level'], $amount);

        return [
            'result'=>true
        ];
    }
}
