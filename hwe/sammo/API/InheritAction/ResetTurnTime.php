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

        $gameStor = KVStorage::getStorage($db, 'game_env');
        [$turnTerm, $serverTurnTime] = $gameStor->getValuesAsArray(['turnterm', 'turntime']);

        $currTurnTime = new DateTimeImmutable($general->getTurnTime());
        $serverTurnTimeObj = new DateTimeImmutable($serverTurnTime);

        $afterTurn = Util::randRange($turnTerm * -60 / 2, $turnTerm * 60 / 2);

        $userLogger = new UserLogger($userID);
        if($afterTurn >= 0){
            $userLogger->push(sprintf("{$reqPoint} 포인트로 턴 시간을 바꾼 결과 %02d:%02d 뒤로 밀림", intdiv(Util::toInt($afterTurn), 60), $afterTurn%60), "inheritPoint");
        }
        else{
            $userLogger->push(sprintf("{$reqPoint} 포인트로 턴 시간을 바꾼 결과 %02d:%02d 앞으로 당김", intdiv(Util::toInt(-$afterTurn), 60), (-$afterTurn)%60), "inheritPoint");
        }
        $userLogger->flush();

        $turnTime = $currTurnTime->add(TimeUtil::secondsToDateInterval($afterTurn));
        if ($turnTime <= $serverTurnTimeObj && $serverTurnTimeObj <= $currTurnTime) {
            $turnTime = $turnTime->add(TimeUtil::secondsToDateInterval($turnTerm * 60));
        }

        $general->setVar('turntime', TimeUtil::format($turnTime, true));
        $general->setAuxVar('inheritResetTurnTime', $nextLevel);
        $inheritStor->setValue('previous', [$previousPoint - $reqPoint, null]);
        $general->applyDB($db);
        return null;
    }
}
