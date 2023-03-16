<?php

namespace sammo\API\Global;

use LDTO\DTO;
use sammo\APICacheResult;
use sammo\BaseAPI;
use sammo\Enums\APIRecoveryType;
use sammo\GlobalMenu;
use sammo\Session;

class GetGlobalMenu extends BaseAPI
{
  public function getRequiredSessionMode(): int
  {
    return self::NO_SESSION;
  }
  function validateArgs(): ?string
  {
    return null;
  }

  function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    if ($reqEtag !== null) {
      $version = GlobalMenu::version;
      if ($reqEtag == "v{$version}") {
        return null;
      }
    }
    return [
      'result' => true,
      'menu' => array_map(function (DTO $dto) {
        return $dto->toArray();
      }, GlobalMenu::getMenu()),
    ];
  }
  public function tryCache(): ?APICacheResult
  {
    $version = GlobalMenu::version;
    return new APICacheResult(
      null,
      "v{$version}",
      60 * 60 * 6,
      true
    );
  }
}
