<?php

namespace sammo\API\Message;

use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\GeneralStorKey;
use sammo\Enums\MessageType;
use sammo\KVStorage;
use sammo\Session;
use sammo\Validator;

class ReadLatestMessage extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    //type이 개인 메시지 혹은 외교 메시지여야함
    $v = new Validator($this->args);
    $v->rule('required', ['type', 'msgID'])
      ->rule('in', 'type', [MessageType::private->value, MessageType::diplomacy->value])
      ->rule('int', 'msgID');
    if (!$v->validate()) {
      return $v->errorStr();
    }
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
  }

  public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $db = DB::db();
    $generalID = $session->generalID;
    $generalStor = KVStorage::getStorage($db, "general_{$generalID}");
    $type = MessageType::from($this->args['type']);
    $msgID = $this->args['msgID'];

    $storKey = GeneralStorKey::latestReadPrivateMsg;
    if($type == MessageType::diplomacy){
      $storKey = GeneralStorKey::latestReadDiplomacyMsg;
    }
    $oldValue = $generalStor->getValue($storKey);
    if($oldValue === null || $oldValue < $msgID){
      $generalStor->setValue($storKey, $msgID);
    }

    return null;
  }
}
