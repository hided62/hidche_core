<?php

namespace sammo\DTO;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\Attributes\MapTo;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class VoteComment extends DataTransferObject
{
	public ?int $id;

  #[MapFrom('vote_id')]
	#[MapTo('vote_id')]
  public int $voteID;

  #[MapFrom('general_id')]
	#[MapTo('general_id')]
  public int $generalID;

  #[MapFrom('nation_id')]
	#[MapTo('nation_id')]
  public int $nationID;

  #[MapFrom('nation_name')]
	#[MapTo('nation_name')]
  public string $nationName;

  #[MapFrom('general_name')]
	#[MapTo('general_name')]
  public string $generalName;

  public string $text;
}
