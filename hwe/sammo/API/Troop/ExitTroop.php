<?php

namespace sammo\API\Troop;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;

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

    $troopID = $db->queryFirstField('SELECT troop FROM general WHERE no = %i', $generalID);
    if($troopID == 0){
      return '부대에 소속되어 있지 않습니다.';
    }

    if($generalID != $troopID){
      $db->update('general', [
        'troop' => 0,
      ], '`no` = %i', $generalID);
      return null;
    }

    //부대장이다.
    $db->update('general', [
      'troop' => 0,
    ], '`troop` = %i', $troopID);
    $db->delete('troop', 'troop_leader = %i', $troopID);

    return null;
  }
}
