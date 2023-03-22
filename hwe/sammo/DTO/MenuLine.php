<?php

namespace sammo\DTO;

use LDTO\DTO;

class MenuLine extends DTO{
  public readonly string $type;

  public function __construct(
  ){
    $this->type = 'line';
  }
}