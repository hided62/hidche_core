<?php

namespace sammo\DTO;

use LDTO\Attr\Convert;
use LDTO\Converter\MapConverter;
use sammo\DTO\SelectItem;

//https://json2dto.atymic.dev/

class BettingInfo extends \LDTO\DTO
{
  public function __construct(
    public int $id,
    public string $type,
    public string $name,
    public bool $finished,
    public int $selectCnt,
    public ?bool $isExclusive,
    public bool $reqInheritancePoint,
    public int $openYearMonth,
    public int $closeYearMonth,


    /** @var \sammo\DTO\SelectItem[] */
    #[Convert(MapConverter::class, [SelectItem::class])]
    public array $candidates,
    public ?array $winner,
  ) {
  }
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