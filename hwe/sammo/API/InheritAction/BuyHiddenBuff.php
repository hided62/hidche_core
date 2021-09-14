<?php

namespace sammo\API\InheritAction;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\TimeUtil;
use sammo\TriggerInheritBuff;
use sammo\Validator;

class BuyHiddenBuff extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'type',
            'level',
        ])
            ->rule('integer', 'level')
            ->rule('min', 'level', 1)
            ->rule('max', 'level', TriggerInheritBuff::MAX_STEP)
            ->rule('keyExists', 'type', TriggerInheritBuff::BUFF_KEY_TEXT);

        if (!$v->validate()) {
            return $v->errorStr();
        }
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        //General.aux 쓰므로 lock;
        return static::REQ_GAME_LOGIN;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
        $userID = $session->userID;
        $generalID = $session->generalID;

        $type = $this->args['type'];
        $level = $this->args['level'];

        $general = General::createGeneralObjFromDB($generalID);
        if ($userID != $general->getVar('owner')) {
            return '로그인 상태가 이상합니다. 다시 로그인해 주세요.';
        }

        $inheritBuffList = $general->getAuxVar('inheritBuff') ?? [];
        $prevLevel = $inheritBuffList[$type] ?? 0;

        if ($prevLevel == $level) {
            return '이미 구입했습니다.';
        }
        if ($prevLevel > $level) {
            return '이미 더 높은 등급을 구입했습니다.';
        }

        $reqAmount = GameConst::$inheritBuffPoints[$level] -  GameConst::$inheritBuffPoints[$prevLevel];

        $db = DB::db();
        $inheritStor = KVStorage::getStorage($db, "inheritance_{$userID}");
        $previousPoint = $inheritStor->getValue('previous') ?? 0;
        if ($previousPoint < $reqAmount) {
            return '충분한 유산 포인트를 가지고 있지 않습니다.';
        }

        $inheritBuffList[$type] = $level;
        $general->setAuxVar('inheritBuff', $inheritBuffList);
        $inheritStor->setValue('previous', $previousPoint - $reqAmount);
        $general->flushUpdateValues();
        return null;
    }
}
