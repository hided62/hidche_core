<?php

namespace sammo\API\InheritAction;

use DateTimeImmutable;
use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\RankColumn;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\TimeUtil;
use sammo\UniqueConst;
use sammo\UserLogger;
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

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $userID = $session->userID;
        $generalID = $session->generalID;

        $general = General::createObjFromDB($generalID);
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
        $gameStor = KVStorage::getStorage($db, 'game_env');
        if ($gameStor->isunited) {
            return '이미 천하가 통일되었습니다.';
        }
        $inheritStor = KVStorage::getStorage($db, "inheritance_{$userID}");
        $previousPoint = ($inheritStor->getValue('previous') ?? [0, 0])[0];
        if ($previousPoint < $reqPoint) {
            return '충분한 유산 포인트를 가지고 있지 않습니다.';
        }

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $turnTerm = $gameStor->getValue('turnterm');

        $currTurnTime = new DateTimeImmutable($general->getTurnTime());

        $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
            UniqueConst::$hiddenSeed,
            'ResetTurnTime',
            $userID,
            $general->getTurnTime()
        )));

        $afterTurn = $rng->nextFloat1() * $turnTerm * 60;

        $userLogger = new UserLogger($userID);
        $userLogger->push(sprintf("{$reqPoint} 포인트로 턴 시간을 바꾸어 다음 턴부터 %02d:%02d 적용", intdiv(Util::toInt($afterTurn), 60), $afterTurn % 60), "inheritPoint");
        $userLogger->flush();

        $general->setAuxVar('inheritResetTurnTime', $nextLevel);
        $general->setAuxVar('nextTurnTimeBase', $afterTurn);
        $inheritStor->setValue('previous', [$previousPoint - $reqPoint, null]);
        $general->increaseRankVar(RankColumn::inherit_point_spent_dynamic, $reqPoint);
        $general->applyDB($db);
        return null;
    }
}
