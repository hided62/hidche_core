<?php

namespace sammo\DTO;

use sammo\DTO\SelectItem;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\Casters\ArrayCaster;

//https://json2dto.atymic.dev/



#[Strict]
class BettingInfo extends DataTransferObject
{
	public int $id;
  public string $type;
	public string $name;
	public bool $finished;
	public int $selectCnt;
  public ?bool $isExlusive;
	public bool $reqInheritancePoint;
	public int $openYearMonth;
	public int $closeYearMonth;

  /** @var \sammo\DTO\SelectItem[] */
  #[CastWith(ArrayCaster::class, itemType: SelectItem::class)]
  public array $candidates;
  public ?array $winner;
}



/*
{
  "id": 45,
  "name": "1차전",
  "finished": false,
  "selectCnt": 1,
  "reqInheritancePoint": true,
  "openYearMonth": 110,
  "closeYearMonth": 120
}
*/