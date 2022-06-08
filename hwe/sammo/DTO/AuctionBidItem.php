<?php

namespace sammo\DTO;

use sammo\DTO\Attr\Convert;
use sammo\DTO\Attr\JsonString;
use sammo\DTO\Attr\NullIsUndefined;
use sammo\DTO\Attr\RawName;
use sammo\DTO\Converter\DateTimeConverter;

class AuctionBidItem extends DTO
{
  public function __construct(
    #[NullIsUndefined]
    public ?int $no,
    #[RawName('auction_id')]
    public int $auctionID,
    public ?int $owner,

    #[RawName('general_id')]
    public int $generalID,

    public int $amount,

    #[Convert(DateTimeConverter::class)]
    public \DateTimeImmutable $date,
    #[JsonString]
    public AuctionBidItemData $aux,
  ) {
  }
}
