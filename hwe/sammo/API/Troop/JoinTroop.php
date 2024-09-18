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
    $generalID = $session->generalID;
    $troopID = $this->args['troopID'];

    $db = DB::db();

    $me = GeneralLite::createObjFromDB($generalID, ['troop'], GeneralLiteQueryMode::Lite);
    if(!$me){
      return '장수 정보를 불러올 수 없습니다.';
    }

    if($me->getVar('troop') != 0){
      return '이미 부대에 소속되어 있습니다.';
    }

    $nationID = $me->getNationID();
    if($nationID == 0){
      return '국가에 소속되어 있지 않습니다.';
    }

    $troopExists = $db->queryFirstField('SELECT `troop_leader` FROM `troop` WHERE `troop_leader` = %i AND `nation` = %i', $troopID, $nationID);
    if (!$troopExists) {
      return '부대가 올바르지 않습니다.';
    }

    $me->setVar('troop', $troopID);
    StaticEventHandler::handleEvent($me, null, $this::class, [], $this->args);
    $me->applyDB($db);

    return null;
  }
}
