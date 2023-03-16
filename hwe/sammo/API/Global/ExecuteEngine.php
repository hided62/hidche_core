<?php

namespace sammo\API\Global;

use sammo\Session;
use sammo\DB;
use DateTimeInterface;
use sammo\Enums\APIRecoveryType;
use sammo\TurnExecutionHelper;
use sammo\UniqueConst;

class ExecuteEngine extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::NO_SESSION;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
      $reqServerID = $this->args['serverID'] ?? null;
      if($reqServerID && $reqServerID !== UniqueConst::$serverID){
        return [
          'result' => false,
          'reason' => '서버 아이디가 다릅니다',
          'reqRefresh' => true,
        ];
      }

      $updated = false;
      $locked = false;
      $lastExecuted = TurnExecutionHelper::executeAllCommand($updated, $locked);
      return [
        'result' => true,
        'updated' => $updated,
        'locked' => $locked,
        'lastExecuted' => $lastExecuted,
      ];
    }
}
