<?php

namespace sammo\DTO;

use LDTO\Attr\Convert;
use LDTO\Attr\NullIsUndefined;
use LDTO\Converter\DateTimeConverter;

class AuctionInfoDetail extends \LDTO\DTO
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
