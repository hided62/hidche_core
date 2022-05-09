<?php

namespace sammo\API\InheritAction;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\RankColumn;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\UserLogger;
use sammo\Validator;

use function sammo\buildGeneralSpecialWarClass;

class SetNextSpecialWar extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'type',
        ])
            ->rule('in', 'type', GameConst::$availableSpecialWar);

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

        $general = General::createGeneralObjFromDB($generalID);
        if ($userID != $general->getVar('owner')) {
            return '로그인 상태가 이상합니다. 다시 로그인해 주세요.';
        }

        $inheritSpecificSpecialWar = $general->getAuxVar('inheritSpecificSpecialWar');
        $currentSpecialWar = $general->getVar('special2');

        if ($currentSpecialWar == $type) {
            return '이미 그 특기를 보유하고 있습니다.';
        }
        if ($inheritSpecificSpecialWar == $type) {
            return '이미 그 특기를 예약하였습니다.';
        }

        if ($inheritSpecificSpecialWar !== null) {
            return '이미 예약한 특기가 있습니다.';
        }

        $reqAmount = GameConst::$inheritSpecificSpecialPoint;

        $db = DB::db();
        $inheritStor = KVStorage::getStorage($db, "inheritance_{$userID}");
        $previousPoint = ($inheritStor->getValue('previous') ?? [0, 0])[0];
        if ($previousPoint < $reqAmount) {
            return '충분한 유산 포인트를 가지고 있지 않습니다.';
        }

        $userLogger = new UserLogger($userID);
        $specialWarObj = buildGeneralSpecialWarClass($type);
        $userLogger->push("{$reqAmount} 포인트로 다음 전투 특기로 {$specialWarObj->getName()} 지정", "inheritPoint");
        $userLogger->flush();

        $general->setAuxVar('inheritSpecificSpecialWar', $type);
        $inheritStor->setValue('previous', [$previousPoint - $reqAmount, null]);
        $general->increaseRankVar(RankColumn::inherit_point_spent_dynamic, $reqAmount);
        $general->applyDB($db);
        return null;
    }
}
