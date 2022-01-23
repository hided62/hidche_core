<?php

namespace sammo\DTO;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

//https://json2dto.atymic.dev/



#[Strict]
class NationBettingInfo extends DataTransferObject
{
	public int $id;
	public string $name;
	public bool $finished;
	public int $selectCnt;
	public bool $reqInheritancePoint;
	public int $openYearMonth;
	public int $closeYearMonth;
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