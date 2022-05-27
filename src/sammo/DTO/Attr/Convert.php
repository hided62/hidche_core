<?php

namespace sammo\DTO\Attr;

use sammo\DTO\Converter\Converter;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
class Convert
{
  public Converter $converter;
  public function __construct(
    public readonly array $targetTypes,
    public readonly string $converterType,
    ...$args
  ) {
    if(!is_subclass_of($converterType, \sammo\DTO\Converter\Converter::class)){
      throw new \Exception("$converterType is not a subclass of \sammo\DTO\Converter\Converter");
    }
    $this->converter = new $converterType($targetTypes, ...$args);
  }

  public function convertFrom(string|array|int|float|bool|null $raw): mixed
  {
    return $this->converter->convertFrom($raw);
  }

  public function convertTo(mixed $target): string|array|int|float|bool|null {
    return $this->converter->convertTo($target);
  }
}
