<?php

namespace sammo\DTO\Attr;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class JsonString
{
  public function __construct(public readonly bool $emptyItemIsArray = false)
  {
  }
}
