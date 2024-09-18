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

class NewTroop extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', [
      'troopName',
    ])
      ->rule('stringWidthBetween', 'troopName', 1, 18);

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
    $troopName = StringUtil::neutralize($this->args['troopName']);
    if(!$troopName){
      return '부대 이름이 없습니다.';
    }

    $db = DB::db();
    $me = GeneralLite::createObjFromDB($generalID, ['troop'], GeneralLiteQueryMode::Lite);
    $troopID = $me->getVar('troop');
    if($troopID != 0){
      return '이미 부대에 소속되어 있습니다.';
    }
    $nationID = $me->getNationID();
    if($nationID == 0){
      return '국가에 소속되어 있지 않습니다.';
    }

    $db->insert('troop', [
      'name'=>$troopName,
      'troop_leader'=>$generalID,
      'nation'=>$nationID,
    ]);

    if($db->affectedRows() == 0){
      return '부대가 생성되지 않았습니다. 버그일 수 있습니다.';
    }

    $me->setVar('troop', $generalID);
    StaticEventHandler::handleEvent($me, null, $this::class, [], $this->args);
    $me->applyDB($db);

    return null;
  }
}
