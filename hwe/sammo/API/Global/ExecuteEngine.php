<?php

namespace sammo\API\Global;

use sammo\Session;
use DateTimeInterface;
use sammo\TurnExecutionHelper;

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

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
      $updated = TurnExecutionHelper::executeAllCommand();
      return [
        'result' => true,
        'updated' => $updated,
      ];
    }
}
