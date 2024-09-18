<?php

namespace sammo\API\Troop;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\GeneralLiteQueryMode;
use sammo\GeneralLite;
use sammo\StaticEventHandler;

class ExitTroop extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $generalID = $session->generalID;
    $db = DB::db();

    $me = GeneralLite::createObjFromDB($generalID, ['troop'], GeneralLiteQueryMode::Lite);
    if (!$me) {
      return '장수 정보를 불러올 수 없습니다.';
    }

    $troopID = $me->getVar('troop');
    if($troopID == 0){
      return '부대에 소속되어 있지 않습니다.';
    }

    if($troopID != $generalID){
      StaticEventHandler::handleEvent($me, null, $this::class, [
        "isTroopLeader" => false,
      ], $this->args);
      $me->setVar('troop', 0);
      $me->applyDB($db);
      return null;
    }

    //부대장이다.
    StaticEventHandler::handleEvent($me, null, $this::class, [
      "isTroopLeader" => true,
    ], $this->args);
    $db->update('general', [
      'troop' => 0,
    ], '`troop` = %i', $troopID);
    $db->delete('troop', 'troop_leader = %i', $troopID);
    $me->setVar('troop', 0);
    $me->applyDB($db);

    return null;
  }
}
