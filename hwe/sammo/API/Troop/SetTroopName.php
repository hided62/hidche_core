<?php

namespace sammo\API\Troop;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\GeneralLiteQueryMode;
use sammo\GeneralLite;
use sammo\StaticEventHandler;
use sammo\StringUtil;
use sammo\Validator;

use function sammo\checkSecretPermission;

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
    $generalID = $session->generalID;
    $db = DB::db();
    $me = GeneralLite::createObjFromDB($generalID, ['troop', 'permission', 'penalty'], GeneralLiteQueryMode::Lite);
    $permission = checkSecretPermission($me->getRaw(), false);
    $troopID = $this->args['troopID'];

    if($generalID != $troopID && $permission < 4){
      return "권한이 부족합니다.";
    }

    $troopName = StringUtil::neutralize($this->args['troopName']);
    if(!$troopName){
      return '부대 이름이 없습니다.';
    }

    $nationID = $me->getNationID();
    $db->update('troop', [
      'name'=>$troopName
    ], 'troop_leader=%i AND `nation`=%i',$troopID, $nationID);

    if($db->affectedRows() == 0){
      return '부대가 없습니다.';
    }
    StaticEventHandler::handleEvent($me, null, $this::class, [], $this->args);

    return null;
  }
}
