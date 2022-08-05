<?php

namespace sammo\DTO;

use LDTO\Attr\NullIsUndefined;

class AuctionBidItemData extends \LDTO\DTO
{
  public function __construct(
    #[NullIsUndefined]
    public ?string $ownerName,
    public string $generalName,
    #[NullIsUndefined]
    public ?bool $tryExtendCloseDate,
  ) {
  }
}
