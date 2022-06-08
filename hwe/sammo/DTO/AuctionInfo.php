<?php

namespace sammo\DTO;

use sammo\DTO\Attr\Convert;
use sammo\DTO\Attr\JsonString;
use sammo\DTO\Attr\NullIsUndefined;
use sammo\DTO\Attr\RawName;
use sammo\DTO\Converter\DateTimeConverter;
use sammo\Enums\AuctionType;
use sammo\Enums\ResourceType;

class AuctionInfo extends DTO
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
