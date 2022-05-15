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
    $voteInfo = new VoteInfo(...$rawVote);


    $votes = array_map(fn ($arr) => [Json::decode($arr[0]), $arr[1]], $db->queryAllLists(
      'SELECT selection, count(*) AS cnt FROM vote_result WHERE voteID = %i GROUP BY selection',
      $voteID
    ));

    $comments = VoteComment::arrayOf($db->query('SELECT * FROM vote_comment WHERE voteID = %i ORDER BY `id` ASC', $voteID));

    return [
      'result' => true,
      'voteInfo' => $voteInfo,
      'votes' => $votes,
      'comments' => $comments
    ];
  }
}
