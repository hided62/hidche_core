<?php

namespace sammo\API\Vote;

use DateTimeInterface;
use sammo\DB;
use sammo\DTO\VoteComment;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\GeneralLiteQueryMode;
use sammo\Enums\GeneralQueryMode;
use sammo\General;
use sammo\GeneralLite;
use sammo\Session;
use sammo\TimeUtil;
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
      ->rule('int', 'voteID');

    if (!$v->validate()) {
      return $v->errorStr();
    }
    $this->args['voteID'] = (int)$this->args['voteID'];
    return null;
  }

  function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $voteID = $this->args['voteID'];
    $text = mb_substr($this->args['text'], 0, 200);

    $generalID = $session->generalID;
    $general = GeneralLite::createObjFromDB($generalID, null, GeneralLiteQueryMode::Core);
    $generalName = $general->getName();
    $nationID = $general->getNationID();
    $nationName = $general->getStaticNation()['name'];
    $date = TimeUtil::now();


    $comment = new VoteComment(
      id: null,
      voteID: $voteID,
      generalID: $generalID,
      nationID: $nationID,
      nationName: $nationName,
      generalName: $generalName,
      text: $text,
      date: $date
    );

    $db = DB::db();
    $db->insert('vote_comment', $comment->toArray());

    return null;
  }
}
