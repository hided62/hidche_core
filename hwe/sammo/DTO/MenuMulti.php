<?php

namespace sammo\DTO;

use LDTO\Attr\Convert;
use LDTO\DTO;
use LDTO\Converter\ArrayConverter;

class MenuMulti extends DTO{
  public readonly string $type;

  /** @param (MenuItem|MenuLine)[] $subMenu */
  public function __construct(
    public string $name,
    #[Convert(ArrayConverter::class, [DTO::class])]
    public array $subMenu,
  ){
    $this->type = 'multi';
  }
}