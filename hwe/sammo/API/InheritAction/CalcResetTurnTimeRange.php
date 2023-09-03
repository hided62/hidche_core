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

class CalcResetTurnTimeRange extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $userID = $session->userID;
        $generalID = $session->generalID;

        $general = General::createObjFromDB($generalID);
        if ($userID != $general->getVar('owner')) {
            return '로그인 상태가 이상합니다. 다시 로그인해 주세요.';
        }

        $db = DB::db();

        $gameStor = KVStorage::getStorage($db, 'game_env');
        [$turnTerm, $serverTurnTime] = $gameStor->getValuesAsArray(['turnterm', 'turntime']);

        $currTurnTime = new DateTimeImmutable($general->getTurnTime());
        $serverTurnTimeObj = new DateTimeImmutable($serverTurnTime);

        $minTurnTime = $currTurnTime->add(TimeUtil::secondsToDateInterval($turnTerm * -30));
        $maxTurnTime = $currTurnTime->add(TimeUtil::secondsToDateInterval($turnTerm * 30));

        $timeDiff = $serverTurnTimeObj->diff($minTurnTime);
        $timeDiffSec = TimeUtil::DateIntervalToSeconds($timeDiff);
        if($timeDiffSec > 0){
            $minTurnTime = $minTurnTime->add(TimeUtil::secondsToDateInterval($timeDiffSec));
            $maxTurnTime = $maxTurnTime->add(TimeUtil::secondsToDateInterval($timeDiffSec));
        }

        return [
            'result' => true,
            'timeDiffSec' => $timeDiffSec,
            'minTurnTime' => TimeUtil::format($minTurnTime, false),
            'maxTurnTime' => TimeUtil::format($maxTurnTime, false),
        ];
    }
}
