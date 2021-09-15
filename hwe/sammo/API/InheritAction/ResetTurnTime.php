<?php

namespace sammo\API\InheritAction;

use DateTimeImmutable;
use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\TimeUtil;
use sammo\Util;

class ResetTurnTime extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        //KVStrorage, General.aux 모두 쓰므로 lock;
        return static::REQ_GAME_LOGIN;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
        $userID = $session->userID;
        $generalID = $session->generalID;

        $general = General::createGeneralObjFromDB($generalID);
        if ($userID != $general->getVar('owner')) {
            return '로그인 상태가 이상합니다. 다시 로그인해 주세요.';
        }

        $currentLevel = $general->getAuxVar('inheritResetTurnTime') ?? -1;
        $nextLevel = $currentLevel + 1;
        while (count(GameConst::$inheritResetAttrPointBase) <= $nextLevel) {
            $baseLen = count(GameConst::$inheritResetAttrPointBase);
            GameConst::$inheritResetAttrPointBase[] = GameConst::$inheritResetAttrPointBase[$baseLen - 1] + GameConst::$inheritResetAttrPointBase[$baseLen - 2];
        }

        $reqPoint = GameConst::$inheritResetAttrPointBase[$nextLevel];

        $db = DB::db();
        $inheritStor = KVStorage::getStorage($db, "inheritance_{$userID}");
        $previousPoint = ($inheritStor->getValue('previous') ?? [0, 0])[0];
        if ($previousPoint < $reqPoint) {
            return '충분한 유산 포인트를 가지고 있지 않습니다.';
        }

        $gameStor = new KVStorage($db, 'game_env');
        [$turnTerm, $serverTurnTime] = $gameStor->getValuesAsArray(['turnterm', 'turntime']);

        $serverTurnTimeObj = new DateTimeImmutable($serverTurnTime);
        $turnTime = new DateTimeImmutable($general->getTurnTime());

        $afterTurn = Util::randRange($turnTerm * -60 / 2, $turnTerm * 60 / 2);

        $turnTime = $turnTime->add(TimeUtil::secondsToDateInterval($afterTurn));
        if ($turnTime <= $serverTurnTimeObj) {
            $turnTime = $turnTime->add(TimeUtil::secondsToDateInterval($turnTerm * 60));
        }

        $general->setVar('turntime', TimeUtil::format($turnTime, true));
        $general->setAuxVar('inheritResetTurnTime', $nextLevel);
        $inheritStor->setValue('previous', [$previousPoint - $reqPoint, 'ResetTurnTime']);
        $general->applyDB($db);
        return null;
    }
}
