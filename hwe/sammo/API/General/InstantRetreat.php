<?php

namespace sammo\API\General;

use sammo\DB;
use sammo\DummyGeneral;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\GeneralAccessLogColumn;
use sammo\GameConst;
use sammo\Session;
use sammo\General;
use sammo\JosaUtil;
use sammo\KVStorage;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\TimeUtil;
use sammo\UniqueConst;
use sammo\Util;

use function sammo\addTurn;
use function sammo\buildGeneralCommandClass;
use function sammo\increaseRefresh;

class InstantRetreat extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN;
  }

  public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {

    if(!(GameConst::$availableInstantAction['instantRetreat'] ?? false)){
        return '접경귀환을 사용할 수 없는 시나리오입니다.';
    }

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $gameStor->cacheAll();

    $general = General::createObjFromDB($session->generalID);

    if (!$general) {
      return '장수가 없습니다';
    }

    increaseRefresh("접경귀환", 1);

    $commandObj = buildGeneralCommandClass('che_접경귀환', $general, $gameStor->getAll(true));
    $logger = $general->getLogger();

    if (!$commandObj->hasFullConditionMet()) {
        $logger->pushGeneralActionLog($commandObj->getFailString());
        $reason = $commandObj->getFailString();
        return $reason;
    }

    $result = $commandObj->run(new RandUtil(
        new LiteHashDRBG(Util::simpleSerialize(
            UniqueConst::$hiddenSeed,
            'InstantRetreat',
            $general->getID(),
            $gameStor->year,
            $gameStor->month,
            $general->getCityID(),
        ))
    ));

    if (!$result) {
        return '가까운 아국 도시가 없습니다.';
    }

    return null;
  }
}
