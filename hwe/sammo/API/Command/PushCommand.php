<?php

namespace sammo\API\Command;

use sammo\Session;
use DateTimeInterface;
use sammo\Validator;

use function sammo\pushGeneralCommand;

class PushCommand extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'amount',
        ])
            ->rule('integer', 'amount')
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

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
        $amount = $this->args['amount'];
        if($amount == 0){
            return '0은 불가능합니다';
        }
        pushGeneralCommand($session->generalID, $amount);

        return [
            'result'=>true
        ];
    }
}
