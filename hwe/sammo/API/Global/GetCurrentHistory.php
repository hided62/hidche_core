<?php

namespace sammo\API\Global;

use sammo\Session;
use DateTimeInterface;
use sammo\Enums\APIRecoveryType;

use function sammo\getCurrentHistory;

class GetCurrentHistory extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $history = getCurrentHistory();

    return [
      'result' => true,
      'data' => $history,
    ];
  }
}
