<?php

namespace sammo\API\InheritAction;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\TimeUtil;

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
        $inheritStor = KVStorage::getStorage($db, "inheritance_{$userID}");
        $previousPoint = $inheritStor->getValue('previous')??0;
        if($previousPoint < GameConst::$inheritItemRandomPoint){
            return '충분한 유산 포인트를 가지고 있지 않습니다.';
        }

        $general->setAuxVar('inheritRandomUnique', TimeUtil::now());
        $inheritStor->setValue('previous', [$previousPoint - GameConst::$inheritItemRandomPoint, 'BuyRandomUnique']);
        $general->applyDB($db);
        return null;
    }
}
