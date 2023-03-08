<?php

namespace sammo\DTO;

use LDTO\Attr\Convert;
use LDTO\Converter\ArrayConverter;
use LDTO\DTO;

class MenuSplit extends DTO{
  public readonly string $type;

  /** @param MenuItem[] $subMenu */
  public function __construct(
    public MenuItem $main,
    #[Convert(ArrayConverter::class, [DTO::class])]
    public array $subMenu,
  ){
    $this->type = 'split';
  }
}