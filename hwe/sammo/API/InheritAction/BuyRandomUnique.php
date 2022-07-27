<?php

namespace sammo\API\InheritAction;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\RankColumn;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\TimeUtil;
use sammo\UserLogger;

class BuyRandomUnique extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
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

        $general = General::createGeneralObjFromDB($generalID);
        if($userID != $general->getVar('owner')){
            return '로그인 상태가 이상합니다. 다시 로그인해 주세요.';
        }

        if($general->getAuxVar('inheritRandomUnique') !== null){
            return '이미 구입 명령을 내렸습니다. 다음 턴까지 기다려주세요.';
        }

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        if($gameStor->isunited){
            return '이미 천하가 통일되었습니다.';
        }
        $inheritStor = KVStorage::getStorage($db, "inheritance_{$userID}");
        $previousPoint = ($inheritStor->getValue('previous')??[0, 0])[0];
        $reqAmount = GameConst::$inheritItemRandomPoint;
        if($previousPoint < $reqAmount){
            return '충분한 유산 포인트를 가지고 있지 않습니다.';
        }

        $userLogger = new UserLogger($userID);
        $userLogger->push("{$reqAmount} 포인트로 랜덤 유니크 구입", "inheritPoint");
        $userLogger->flush();

        $general->setAuxVar('inheritRandomUnique', TimeUtil::now());
        $inheritStor->setValue('previous', [$previousPoint - $reqAmount, null]);
        $general->increaseRankVar(RankColumn::inherit_point_spent_dynamic, $reqAmount);
        $general->applyDB($db);
        return null;
    }
}
