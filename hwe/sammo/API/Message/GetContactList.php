<?php

namespace sammo\API\Message;

use sammo\Session;
use DateTimeInterface;
use sammo\Enums\APIRecoveryType;

use function sammo\getMailboxList;

class GetContactList extends \sammo\BaseAPI{
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
    if(!$session->generalID){
      return [
        "nation"=>[]
      ];
    }

    //NOTE: 모든 국가, 모든 장수에 대해서 같은 결과라면 캐싱 가능하지 않을까?

    return [
      "nation"=>getMailboxList()
    ];
  }
}