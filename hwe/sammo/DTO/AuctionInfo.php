<?php

namespace sammo\DTO;

use LDTO\Attr\Convert;
use LDTO\Attr\JsonString;
use LDTO\Attr\NullIsUndefined;
use LDTO\Attr\RawName;
use LDTO\Converter\DateTimeConverter;
use sammo\Enums\AuctionType;
use sammo\Enums\ResourceType;

class AuctionInfo extends \LDTO\DTO
{
	public function __construct(
		#[NullIsUndefined]
		public ?int $id,
		public AuctionType $type,
		public bool $finished,
		public ?string $target,
		#[RawName('host_general_id')]
		public int $hostGeneralID,
		#[RawName('req_resource')]
		public ResourceType $reqResource,

		#[RawName('open_date')]
		#[Convert(DateTimeConverter::class)]
		public \DateTimeImmutable $openDate,
		#[RawName('close_date')]
		#[Convert(DateTimeConverter::class)]
		public \DateTimeImmutable $closeDate,

		#[JsonString]
		public AuctionInfoDetail $detail,
	) {
	}
}
