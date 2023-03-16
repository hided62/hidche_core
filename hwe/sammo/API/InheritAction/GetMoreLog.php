<?php

namespace sammo\API\InheritAction;

use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Session;
use sammo\Validator;
use sammo\Util;

class GetMoreLog extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('integer', 'lastID');
    if (!$v->validate()) {
      return $v->errorStr();
    }
    $this->args['lastID'] = Util::toInt($this->args['lastID']);
    return null;
  }
  function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
  }

  function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $userID = $session->userID;
    $lastID = $this->args['lastID'];
    $db = DB::db();
    $lastInheritPointLogs = $db->query('SELECT id, server_id, year, month, date, text FROM user_record WHERE log_type = %s AND `user_id` = %i AND id < %i ORDER BY id desc LIMIT 30', "inheritPoint", $userID, $lastID);

    return [
      'result'=> true,
      'log' => $lastInheritPointLogs,
    ];
  }
}
