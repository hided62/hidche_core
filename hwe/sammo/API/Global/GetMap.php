<?php

namespace sammo\API\Global;

use sammo\Session;
use DateTimeInterface;
use sammo\Enums\APIRecoveryType;
use sammo\MapRequest;
use sammo\Validator;

use function sammo\getWorldMap;

class GetMap extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('in', 'neutralView', [0, 1])
      ->rule('in', 'showMe', [0, 1]);
    if (!$v->validate()) {
      return $v->errorStr();
    }
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_LOGIN | static::REQ_READ_ONLY;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    return getWorldMap(new MapRequest([
      'neutralView' => !!($this->args['neutralView'] ?? false),
      'showMe' => !!($this->args['showMe'] ?? false),
    ]));
  }
}
