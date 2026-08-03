<?php

namespace sammo\API\Vote;

use DateTimeInterface;
use sammo\DB;
use sammo\DTO\VoteInfo;
use sammo\Enums\APIRecoveryType;
use sammo\GameClock;
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
    $clock = GameClock::fromStorage(KVStorage::getStorage($db, 'game_env'));

    $voteStor = KVStorage::getStorage($db, 'vote');

    $votes = [];
    foreach($voteStor->getAll() as $voteKey => $rawVote){
      if(preg_match('/^vote_(\d+)$/D', $voteKey, $matches) !== 1){
        continue;
      }
      $voteID = (int)$matches[1];
      $votes[$voteID] = VoteInfo::fromGameStorage($rawVote, $clock);
    }

    return [
      'result'=>true,
      'votes'=>$votes
    ];
  }
}
