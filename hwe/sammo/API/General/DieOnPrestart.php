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
use sammo\TimeUtil;

use function sammo\addTurn;
use function sammo\increaseRefresh;

class DieOnPrestart extends \sammo\BaseAPI
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
    //로그인 검사
    $userID = $session->userID;


    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $gameStor->cacheValues(['turnterm', 'opentime', 'turntime', 'year', 'month']);

    $general = $db->queryFirstRow('SELECT no,name,nation,owner_name,npc FROM general WHERE owner=%i AND npc = 0', $userID);
    $lastRefresh = $db->queryFirstField(
      'SELECT %b FROM general_access_log WHERE %b = %i',
      GeneralAccessLogColumn::lastRefresh->value,
      GeneralAccessLogColumn::generalID->value,
      $general['no']
    );

    if (!$general) {
      return '장수가 없습니다';
    }

    increaseRefresh("장수 삭제", 1);


    if ($gameStor->turntime > $gameStor->opentime) {
      return '게임이 시작되었습니다.';
    }

    if ($general['nation'] != 0){
      return '이미 국가에 소속되어있습니다.';
    }

    //서버 가오픈시 할 수 있는 행동
    $targetTime = addTurn($lastRefresh, $gameStor->turnterm, GameConst::$minTurnDieOnPrestart);
    if ($targetTime > TimeUtil::now()) {
      $targetTimeShort = substr($targetTime, 0, 19);
      return "아직 삭제할 수 없습니다. {$targetTimeShort} 부터 가능합니다.";
    }

    $generalObj = General::createGeneralObjFromDB($general['no']);
    if ($generalObj instanceof DummyGeneral) {
      trigger_error("올바르지 않은 삭제 프로세스 $userID", E_USER_WARNING);
    }

    $generalName = $generalObj->getName();
    $josaYi = JosaUtil::pick($generalName, '이');
    $generalObj->kill($db, true, "<Y>{$generalName}</>{$josaYi} 홀연히 모습을 <R>감추었습니다</>");

    $session->logoutGame();
    return null;
  }
}
