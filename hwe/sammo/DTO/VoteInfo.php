<?php

namespace sammo\DTO;

use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\Casters\ArrayCaster;

//https://json2dto.atymic.dev/

#[Strict]
class VoteInfo extends DataTransferObject
{
	public int $id;
  public string $title;
  public int $multipleOptions;

  public string $startDate;
  public ?string $endDate;

  /** @var string[] */
  public array $options;
}
