<?php

namespace sammo\API\General;
use sammo\Validator;

use sammo\API\Nation\GetGeneralLog as GetNationGeneralLog;
use sammo\Session;

class GetGeneralLog extends GetNationGeneralLog
{
    const GENERAL_HISTORY = 'generalHistory';
    const GENERAL_ACTION = 'generalAction';
    const BATTLE_RESULT = 'battleResult';
    const BATTLE_DETAIL = 'battleDetail';

    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v
            ->rule('required', [
                'type',
            ])
            ->rule('integer', [
                'reqTo',
            ])
            ->rule('in', 'type', [
                self::GENERAL_HISTORY,
                self::GENERAL_ACTION,
                self::BATTLE_RESULT,
                self::BATTLE_DETAIL,
            ]);

        if (!$v->validate()) {
            return $v->errorStr();
        }

        if (key_exists('reqTo', $this->args)) {
            $this->args['reqTo'] = (int)$this->args['reqTo'];
        }
        return null;
    }

    public function checkPermission(array $me): ?string
    {
        return null;
    }

    public function getTargetGeneralID(Session $session): int{
        return $session->generalID;
    }
}
