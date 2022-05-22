<?php

namespace sammo\DTO;

use sammo\DTO\Attr\NullIsUndefined;
use sammo\DTO\Attr\RawName;

class BettingItem extends DTO
{
	public function __construct(
		#[RawName('id')]
		#[NullIsUndefined]
		public ?int $rowID,

		#[RawName('betting_id')]
		public int $bettingID,

		#[RawName('general_id')]
		public int $generalID,

		#[RawName('user_id')]
		public ?int $userID,

		#[RawName('betting_type')]
		public string $bettingType,
		public int $amount,
	) {
	}
}
