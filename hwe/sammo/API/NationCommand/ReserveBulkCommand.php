<?php

namespace sammo\API\NationCommand;

use sammo\Session;
use DateTimeInterface;
use sammo\Enums\APIRecoveryType;
use sammo\GameConst;
use sammo\Util;
use sammo\Validator;

use function sammo\setNationCommand;

class ReserveBulkCommand extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        foreach ($this->args as $idx => $turn) {
            $v = new Validator($turn);
            $v->rule('required', [
                'action',
                'turnList'
            ])
                ->rule('lengthMin', 'action', 1)
                ->rule('integerArray', 'turnList');

            if (!$v->validate()) {
                return "{$idx}:{$v->errorStr()}";
            }
        }

        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $briefList = [];
        foreach ($this->args as $idx => $turn) {
            $action = $turn['action'];
            $turnList = $turn['turnList'];
            $arg = $turn['arg'] ?? [];

            if (!$turnList) {
                return "{$idx}: 턴이 입력되지 않았습니다";
            }

            if (!in_array($action, Util::array_flatten(GameConst::$availableChiefCommand))) {
                return "{$idx}: 사용할 수 없는 커맨드입니다.";
            }

            if (!is_array($arg)) {
                return "{$idx}: 올바른 arg 형태가 아닙니다.";
            }
            $partialResult = setNationCommand($session->generalID, $turnList, $action, $arg);
            if(!$partialResult['result']){
                return [
                    'result' => false,
                    'briefList' => $briefList,
                    'errorIdx' => $idx,
                    'reason' => $partialResult['reason']
                ];
            }
            $briefList[$idx] = $partialResult['brief'];
        }

        return [
            'result' => true,
            'briefList' => $briefList,
            'reason' => 'success'
        ];
    }
}
