<?php

namespace sammo\DTO;

use sammo\DTO\Attr\NullIsUndefined;

class AuctionBidItemData extends DTO
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
