<?php

namespace sammo\DTO;

use LDTO\Attr\NullIsUndefined;
use LDTO\Attr\RawName;

class VoteComment extends \LDTO\DTO
{
  public function __construct(
    #[NullIsUndefined]
    public ?int $id,

    #[RawName('vote_id')]
    public int $voteID,

    #[RawName('general_id')]
    public int $generalID,

    #[RawName('nation_id')]
    public int $nationID,

    #[RawName('nation_name')]
    public string $nationName,

    #[RawName('general_name')]
    public string $generalName,

    public string $text,

    public string $date,
  ) {
  }
}
