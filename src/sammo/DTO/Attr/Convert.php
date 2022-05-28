<?php

namespace sammo\DTO\Attr;

use sammo\DTO\Converter\Converter;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
class Convert
{
  public Converter $converter;
  public array $targetTypes;
  public readonly array $args;
  public function __construct(
    public readonly string $converterType,
    ...$args
  ) {
    if(!is_subclass_of($converterType, \sammo\DTO\Converter\Converter::class)){
      throw new \Exception("$converterType is not a subclass of DTO\Converter\Converter");
    }
    $this->args = $args;
  }

  public function setType(array $targetTypes): self{
    $this->targetTypes = $targetTypes;
    $converterType = $this->converterType;
    $this->converter = new $converterType($targetTypes, ...$this->args);
    return $this;
  }

  public function convertFrom(string|array|int|float|bool|null $raw): mixed
  {
    if($this->converter === null){
      throw new \Exception('converter is not set');
    }
    return $this->converter->convertFrom($raw);
  }

  public function convertTo(mixed $target): string|array|int|float|bool|null {
    if($this->converter === null){
      throw new \Exception('converter is not set');
    }
    return $this->converter->convertTo($target);
  }
}
