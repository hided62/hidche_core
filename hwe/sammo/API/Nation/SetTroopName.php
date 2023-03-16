<?php

namespace sammo\API\Nation;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\StringUtil;
use sammo\Validator;

use function sammo\checkSecretPermission;

/** @deprecated */
class SetTroopName extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', [
      'troopID',
      'troopName',
    ])
      ->rule('stringWidthBetween', 'troopName', 1, 18)
      ->rule('integer', 'troopID');

    if (!$v->validate()) {
      return $v->errorStr();
    }
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $userID = $session->userID;
    $db = DB::db();
    $me = $db->queryFirstRow('SELECT `no`,nation,`officer_level`,permission,penalty FROM general WHERE `owner`=%i', $userID);
    $permission = checkSecretPermission($me, false);
    $troopID = $this->args['troopID'];

    $generalID = $me['no'];
    if($generalID != $troopID && $permission < 4){
      return "권한이 부족합니다.";
    }

    $troopName = StringUtil::neutralize($this->args['troopName']);
    if(!$troopName){
      return '부대 이름이 없습니다.';
    }

    $nationID = $me['nation'];
    $db->update('troop', [
      'name'=>$troopName
    ], 'troop_leader=%i AND `nation`=%i',$troopID, $nationID);

    if($db->affectedRows() == 0){
      return '부대가 없습니다.';
    }

    return null;
  }
}
