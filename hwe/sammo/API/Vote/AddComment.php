<?php

namespace sammo\API\Vote;

use DateTimeInterface;
use sammo\DB;
use sammo\DTO\VoteComment;
use sammo\General;
use sammo\Session;
use sammo\Validator;

class AddComment extends \sammo\BaseAPI
{
  function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
  }

  function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', [
      'voteID',
      'text',
    ])->rule('lengthMin', 'text', 1)
      ->rule('int', 'multipleOptions');

    if (!$v->validate()) {
      return $v->errorStr();
    }
    return null;
  }

  function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
  {
    $voteID = $this->args['voteID'];
    $text = $this->args['text'];

    $generalID = $session->generalID;
    $general = General::createGeneralObjFromDB($generalID, [], 0);
    $generalName = $general->getName();
    $nationID = $general->getNationID();
    $nationName = $general->getStaticNation()['name'];

    $comment = new VoteComment(
      voteID: $voteID,
      generalID: $generalID,
      nationID: $nationID,
      nationName: $nationName,
      generalName: $generalName,
      text: $text,
    );

    $db = DB::db();
    $db->insert('vote_comment', $comment->toArray());

    return null;
  }
}
