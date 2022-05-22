<?php

namespace sammo\API\Vote;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\DTO\VoteComment;
use sammo\DTO\VoteInfo;
use sammo\Json;
use sammo\KVStorage;
use sammo\Validator;

class GetVoteDetail extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', 'voteID')
      ->rule('integer', 'voteID');
    if (!$v->validate()) {
      return $v->errorStr();
    }
    $this->args['voteID'] = (int)$this->args['voteID'];
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_LOGIN | static::REQ_READ_ONLY;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
  {
    $voteID = $this->args['voteID'];
    $db = DB::db();

    $voteStor = KVStorage::getStorage($db, 'vote');
    $rawVote = $voteStor->getValue("vote_{$voteID}");
    if (!$rawVote) {
      return '설문조사가 없습니다.';
    }
    $voteInfo = VoteInfo::fromArray($rawVote);


    $votes = array_map(fn ($arr) => [Json::decode($arr[0]), $arr[1]], $db->queryAllLists(
      'SELECT selection, count(*) AS cnt FROM vote WHERE vote_id = %i GROUP BY selection',
      $voteID
    ));

    $comments = array_map(fn ($arr) => VoteComment::fromArray($arr), $db->query('SELECT * FROM vote_comment WHERE vote_id = %i ORDER BY `id` ASC', $voteID));

    $myVote = null;
    if ($session->isGameLoggedIn()) {
      $generalID = $session->generalID;
      $rawMyVote = $db->queryFirstField('SELECT selection FROM vote WHERE vote_id = %i AND general_id = %i', $voteID, $generalID);
      if ($rawMyVote) {
        $myVote = Json::decode($rawMyVote);
      }
    }

    $userCnt = $db->queryFirstField('SELECT count(*) FROM general WHERE npc < 2');


    return [
      'result' => true,
      'voteInfo' => $voteInfo,
      'votes' => $votes,
      'comments' => $comments,
      'myVote' => $myVote,
      'userCnt' => $userCnt,
    ];
  }
}
