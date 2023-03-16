<?php

namespace sammo\API\Vote;

use DateTimeInterface;
use sammo\DB;
use sammo\DTO\VoteInfo;
use sammo\Enums\APIRecoveryType;
use sammo\KVStorage;
use sammo\Session;

class GetVoteList extends \sammo\BaseAPI
{

  public function validateArgs(): ?string
  {
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_LOGIN | static::REQ_READ_ONLY;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $db = DB::db();

    $voteStor = KVStorage::getStorage($db, 'vote');

    $votes = [];
    foreach($voteStor->getAll() as $voteKey => $rawVote){
      if(!str_starts_with($voteKey, 'vote_')){
        continue;
      }
      $voteID = (int)substr($voteKey, 5);
      $votes[$voteID] = VoteInfo::fromArray($rawVote);
    }

    return [
      'result'=>true,
      'votes'=>$votes
    ];
  }
}
