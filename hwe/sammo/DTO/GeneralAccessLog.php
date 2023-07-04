<?php

namespace sammo\DTO;

use LDTO\Attr\Convert;
use LDTO\Attr\NullIsUndefined;
use LDTO\Attr\RawName;
use LDTO\Converter\DateTimeConverter;

class GeneralAccessLog extends \LDTO\DTO
{
  public function __construct(
    #[NullIsUndefined]
    public ?int $id,

    #[RawName('general_id')]
    public int $generalID,

    #[RawName('user_id')]
    public ?int $userID,

    #[RawName('nation_id')]
    #[NullIsUndefined]
    public ?int $nationID,

    #[RawName('last_refresh')]
    #[Convert(DateTimeConverter::class)]
    public \DateTimeImmutable $lastRefresh,

    #[RawName('last_connect')]
    #[Convert(DateTimeConverter::class)]
    public ?\DateTimeImmutable $lastConnect,

    #[RawName('login_total')]
    public int $loginTotal,

    public int $refresh,

    #[RawName('refresh_total')]
    public int $refreshTotal,
  ) {
  }
}