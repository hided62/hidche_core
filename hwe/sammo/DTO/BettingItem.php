<?php

namespace sammo\DTO;

use LDTO\Attr\NullIsUndefined;
use LDTO\Attr\RawName;

class BettingItem extends \LDTO\DTO
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
