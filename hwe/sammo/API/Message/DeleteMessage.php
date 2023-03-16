<?php

namespace sammo\API\Message;

use sammo\Session;
use DateTimeInterface;
use sammo\Enums\APIRecoveryType;
use sammo\Message;
use sammo\Validator;

class DeleteMessage extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', ['msgID']);
    $v->rule('integer', 'msgID');
    if (!$v->validate()) {
      return $v->errorStr();
    }
    $this->args['msgID'] = (int)($this->args['msgID'] ?? -1);

    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $msgID = $this->args['msgID'];

    $generalID = $session->generalID;
    return Message::deleteMsg($msgID, $generalID);
  }
}
