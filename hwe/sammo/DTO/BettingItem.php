<?php

namespace Sammo\DTO;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class BettingItem extends DataTransferObject
{
    #[MapFrom('id')]
	public null|int $rowID = null;
    #[MapFrom('betting_id')]
	public int $bettingID;
    #[MapFrom('general_id')]
	public int $generalID;
    #[MapFrom('user_id')]
	public null|int $userID;
    #[MapFrom('betting_type')]
	public string $bettingType;
	public int $amount;
}
