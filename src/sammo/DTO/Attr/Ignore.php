<?php

namespace sammo\DTO\Attr;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Ignore
{
  public function __construct()
  {
  }
}
