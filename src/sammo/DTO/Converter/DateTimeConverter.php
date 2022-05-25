<?php

namespace sammo\DTO\Converter;

use sammo\TimeUtil;

class DateTimeConverter implements Converter
{
  protected bool $useFraction;
  public function __construct(private array $types, ...$args)
  {
    if(count($args) > 0) {
      if(!is_bool($args[0])) {
        throw new \Exception('DateTimeConverter constructor argument must be boolean');
      }
      $this->useFraction = $args[0];
    } else {
      $this->useFraction = false;
    }
  }

  public function convertFrom(string|array|int|float|bool|null $raw): mixed
  {
    if ($raw === null && array_search('null', $this->types, true) !== false) {
      return null;
    }
    if (!is_string($raw)){
      throw new \Exception('DateTimeConverter can not convert non-string');
    }
    if (array_search('DateTime', $this->types, true) === false) {
      return new \DateTime($raw);
    }
    return new \DateTimeImmutable($raw);
  }

  public function convertTo(mixed $data): string|array|int|float|bool|null
  {
    if ($data === null && array_search('null', $this->types, true) !== false) {
      return null;
    }
    if (!$data instanceof \DateTimeInterface) {
      throw new \Exception('DateTimeConverter can not convert non-DateTimeInterface');
    }
    return TimeUtil::format($data, $this->useFraction);
  }
}
