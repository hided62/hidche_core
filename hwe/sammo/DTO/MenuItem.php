<?php

namespace sammo\DTO;

use LDTO\Attr\NullIsUndefined;
use LDTO\DTO;

class MenuItem extends DTO{
  public readonly string $type;

  public function __construct(
    public readonly string $name,
    public readonly string $url,

    #[NullIsUndefined]
    public ?string $funcCall = null,

    #[NullIsUndefined]
    public ?string $icon = null,
    #[NullIsUndefined]
    public ?bool $newTab = false,
    #[NullIsUndefined]
    public ?string $condHighlightVar = null,
    #[NullIsUndefined]
    public ?string $condShowVar = null,
  ){
    $this->type = 'item';
  }
}