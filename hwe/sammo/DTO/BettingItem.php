<?php

namespace sammo\DTO;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\Attributes\MapTo;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class BettingItem extends DataTransferObject
{
    #[MapFrom('id')]
	#[MapTo('id')]
	public null|int $rowID = null;

    #[MapFrom('betting_id')]
	#[MapTo('betting_id')]
	public int $bettingID;

    #[MapFrom('general_id')]
	#[MapTo('general_id')]
	public int $generalID;

    #[MapFrom('user_id')]
	#[MapTo('user_id')]
	public null|int $userID;

    #[MapFrom('betting_type')]
	#[MapTo('betting_type')]
	public string $bettingType;
	public int $amount;
}
