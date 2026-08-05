<?php

namespace sammo\DTO;

use LDTO\Attr\NullIsUndefined;
use LDTO\Attr\RawName;

class GeneralAccessLog extends \LDTO\DTO
{
  public function __construct(
    #[NullIsUndefined]
    public ?int $id,

    #[RawName('general_id')]
    public int $generalID,

    #[RawName('user_id')]
    public ?int $userID,

    #[RawName('last_refresh')]
    public ?int $lastRefresh,

    public int $refresh,

    #[RawName('refresh_total')]
    public int $refreshTotal,

    #[RawName('refresh_score')]
    public int $refreshScore,

    #[RawName('refresh_score_total')]
    public int $refreshScoreTotal,
  ) {
  }
}
