<?php

namespace sammo\API\Troop;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\StringUtil;
use sammo\Validator;

class JoinTroop extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', [
      'troopID',
    ])
      ->rule('integer', 'troopID');

    if (!$v->validate()) {
      return $v->errorStr();
    }
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $userID = $session->userID;
    $troopID = $this->args['troopID'];

    $db = DB::db();
    $me = $db->queryFirstRow('SELECT `no`,nation,`troop`,`officer_level`,permission,penalty FROM general WHERE `owner`=%i', $userID);
    if ($me['troop'] != 0) {
      return '이미 부대에 소속되어 있습니다.';
    }
    $nationID = $me['nation'];
    if ($nationID == 0) {
      return '국가에 소속되어 있지 않습니다.';
    }

    $troopExists = $db->queryFirstField('SELECT `troop_leader` FROM `troop` WHERE `troop_leader` = %i AND `nation` = %i', $troopID, $nationID);
    if (!$troopExists) {
      return '부대가 올바르지 않습니다.';
    }
    $generalID = $me['no'];

    $db->update('general', [
      'troop' => $troopID,
    ], '`no` = %i AND `troop` = %i', $generalID, 0);

    return null;
  }
}
