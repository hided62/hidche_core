<?php

namespace sammo\DTO;

use sammo\DTO\Attr\Convert;
use sammo\DTO\Attr\NullIsUndefined;
use sammo\DTO\Converter\DateTimeConverter;

class AuctionInfoDetail extends DTO
{
	public function __construct(
		public string $title,
		public string $hostName,
		public int $amount,
		#[NullIsUndefined]
		public ?bool $isReverse,

		public int $startBidAmount,
		#[NullIsUndefined]
		public ?int $finishBidAmount,
		#[NullIsUndefined]
		public ?int $remainCloseDateExtensionCnt,
		#[NullIsUndefined]
		#[Convert(DateTimeConverter::class)]
		public ?\DateTimeImmutable $availableLatestBidCloseDate,
	) {
	}
}
