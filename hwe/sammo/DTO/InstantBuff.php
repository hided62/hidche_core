<?php

namespace sammo\DTO;

use LDTO\DTO;

class InstantBuff extends DTO{
  public function __construct(
    public readonly string $buffName,
    public readonly int $untilYearMonth,
  ){
  }
}