<?php

namespace sammo\DTO;

use LDTO\Attr\Convert;
use LDTO\Attr\JsonString;
use LDTO\Attr\NullIsUndefined;
use LDTO\Attr\RawName;
use LDTO\Converter\DateTimeConverter;

class AuctionBidItem extends \LDTO\DTO
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
