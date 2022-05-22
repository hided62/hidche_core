<?php

namespace sammo\DTO\Converter;

class ArrayConverter implements Converter
{
  private Converter $itemConverter;
  public function __construct(private array $types, ...$args)
  {
    $itemTypes = array_shift($args);
    if(!is_array($itemTypes)){
      throw new \Exception('itemTypes is not a array');
    }
    $itemConverterType = array_shift($args);
    if($itemConverterType === null){
      $itemConverterType = DefaultConverter::class;
    }
    else if(!is_subclass_of($itemConverterType, Converter::class)){
      throw new \Exception("$itemConverterType is not a subclass of \sammo\DTO\Converter\Converter");
    }
    $this->itemConverter = new $itemConverterType($itemTypes, ...$args);
  }

  public function convertFrom(string|array|int|float|bool|null $raw): mixed
  {
    if ($raw === null && array_search('null', $this->types, true) !== false) {
      return null;
    }
    if (!is_array($raw) || !array_is_list($raw)) {
      throw new \Exception('value is not a array');
    }
    return array_map(fn ($v) => $this->itemConverter->convertFrom($v), $raw);
  }

  public function convertTo(mixed $data): string|array|int|float|bool|null
  {
    if ($data === null && array_search('null', $this->types) !== false) {
      return null;
    }
    if (!is_array($data) || !array_is_list($data)) {
      throw new \Exception('value is not a array');
    }
    return array_map(fn ($v) => $this->itemConverter->convertTo($v), $data);
  }
}
