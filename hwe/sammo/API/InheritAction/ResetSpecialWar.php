<?php

namespace sammo\API\InheritAction;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\Validator;

class ResetSpecialWar extends \sammo\BaseAPI
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


        $currentSpecialWar = $general->getVar('special2');
        if ($currentSpecialWar === null || $currentSpecialWar == 'None') {
            return '이미 전투 특기가 공란입니다.';
        }

        $currentLevel = $general->getAuxVar('inheritResetSpecialWar') ?? -1;
        $nextLevel = $currentLevel + 1;
        while (count(GameConst::$inheritResetAttrPointBase) <= $nextLevel) {
            $baseLen = count(GameConst::$inheritResetAttrPointBase);
            GameConst::$inheritResetAttrPointBase[] = GameConst::$inheritResetAttrPointBase[$baseLen - 1] + GameConst::$inheritResetAttrPointBase[$baseLen - 2];
        }

        $reqPoint = GameConst::$inheritResetAttrPointBase[$nextLevel];

        $db = DB::db();
        $inheritStor = KVStorage::getStorage($db, "inheritance_{$userID}");
        $previousPoint = $inheritStor->getValue('previous') ?? 0;
        if ($previousPoint < $reqPoint) {
            return '충분한 유산 포인트를 가지고 있지 않습니다.';
        }

        $oldTypeKey = 'prev_types_special2';
        $oldSpecialList = $general->getAuxVar($oldTypeKey) ?? [];
        $oldSpecialList[] = $currentSpecialWar;
        $general->setAuxVar($oldTypeKey, $oldSpecialList);

        $general->setAuxVar('inheritResetSpecialWar', $nextLevel);
        $general->setVar('special2', 'None');
        $inheritStor->setValue('previous', $previousPoint - $reqPoint);
        $general->flushUpdateValues();
        return null;
    }
}
