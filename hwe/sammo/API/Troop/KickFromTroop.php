<?php

namespace sammo\API\Troop;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Validator;

class KickFromTroop extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', [
      'troopID',
      'generalID',
    ])
      ->rule('integer', 'troopID')
      ->rule('integer', 'generalID');

      if (!$v->validate()) {
        return $v->errorStr();
      }
      return null;
    }


  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
  {
    $generalID = $this->args['generalID'];
    $db = DB::db();

    $troopID = $db->queryFirstField('SELECT troop FROM general WHERE no = %i', $generalID);
    if($troopID == 0){
      return '부대에 소속되어 있지 않습니다.';
    }

    if($troopID != $this->args['troopID']){
      return '다른 부대에 소속되어 있습니다.';
    }

    if($troopID == $generalID){
      return '부대장을 추방할 수 없습니다.';
    }

    $db->update('general', [
      'troop' => 0,
    ], '`no` = %i AND `troop` = %i', $generalID, $troopID);
    return null;
  }
}
