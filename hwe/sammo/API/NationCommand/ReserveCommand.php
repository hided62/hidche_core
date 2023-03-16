<?php

namespace sammo\API\NationCommand;

use sammo\Session;
use DateTimeInterface;
use sammo\Enums\APIRecoveryType;
use sammo\GameConst;
use sammo\Util;
use sammo\Validator;

use function sammo\setNationCommand;

class ReserveCommand extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'action',
            'turnList'
        ])
            ->rule('lengthMin', 'action', 1)
            ->rule('integerArray', 'turnList');

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
        $action = $this->args['action'];
        $turnList = $this->args['turnList'];
        $arg = $this->args['arg']??[];

        if(!$turnList){
            return '턴이 입력되지 않았습니다';
        }

        if(!in_array($action, Util::array_flatten(GameConst::$availableChiefCommand))){
            return '사용할 수 없는 커맨드입니다.';
        }

        if(!is_array($arg)){
            '올바른 arg 형태가 아닙니다.';
        }

        return setNationCommand($session->generalID, $turnList, $action, $arg);
    }
}
