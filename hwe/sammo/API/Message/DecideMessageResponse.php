<?php

namespace sammo\API\Message;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\DiplomaticMessage;
use sammo\KVStorage;
use sammo\Message;
use sammo\Validator;

class DecideMessageResponse extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', ['msgID', 'response']);
    $v->rule('integer', 'msgID')
      ->rule('boolean', 'response');
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

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
  {
    $msgID = $this->args['msgID'];
    $msgResponse = $this->args['response'];

    $generalID = $session->generalID;

    $msg = Message::getMessageByID($msgID);
    if ($msg === null) {
      return '존재하지 않는 메시지입니다.';
    }

    $reason = 'success';
    $gameStor = KVStorage::getStorage(DB::db(), 'game_env');
    $gameStor->cacheAll();
    if ($msgResponse) {
      $result = $msg->agreeMessage($generalID, $reason);
    } else {
      $result = $msg->declineMessage($generalID, $reason);
    }


    return [
      'result' => $result !== DiplomaticMessage::INVALID,
      'reason' => $reason
    ];
  }
}
