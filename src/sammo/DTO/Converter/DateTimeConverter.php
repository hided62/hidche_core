<?php

namespace sammo\DTO\Converter;

use sammo\TimeUtil;

class DateTimeConverter implements Converter
{
  protected bool $useFraction;
  protected \DateTimeZone $timeZoneOffset;
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

    if(count($args) > 1) {
      $args1 = $args[1];
      if(is_int($args1)){
        if($args1 < -12 || $args1 > 14){
          throw new \Exception('TimeZone argument must be between -12 and 14');
        }
        if($args1 > 0){
          $offset = sprintf('+%02d00', $args1);
        }
        else{
          $offset = sprintf('-%02d00', abs($args1));
        }
        $this->timeZoneOffset = new \DateTimeZone($offset);
      } else if(is_float($args1)) {
        if($args1 < -12 || $args1 > 14){
          throw new \Exception('TimeZone argument must be between -12 and 14');
        }
        $isPositive = $args1 > 0;
        $offset = abs($args1);
        $hour = floor($offset);
        $minute = floor(($offset - $hour) * 60);
        if($isPositive){
          $offset = sprintf('+%02d%02d', $hour, $minute);
        } else {
          $offset = sprintf('-%02d%02d', $hour, $minute);
        }
        $this->timeZoneOffset = new \DateTimeZone($offset);
      }
      else if(is_string($args1)){
        $this->timeZoneOffset = new \DateTimeZone($args1);
      } else if($args1 instanceof \DateTimeZone){
        $this->timeZoneOffset = $args1;
      } else if($args1 === null){
        $this->timeZoneOffset = new \DateTimeZone(\date_default_timezone_get());
      } else {
        throw new \Exception('DateTimeConverter constructor argument must be int|string|\DateTimeZone');
      }
    }
    else{
      $this->timeZoneOffset = new \DateTimeZone(\date_default_timezone_get());
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
    if (array_search('DateTimeImmutable', $this->types, true) !== false) {
      $objDateTime = new \DateTimeImmutable($raw, $this->timeZoneOffset);
    }
    else if (array_search('DateTime', $this->types, true) !== false) {
      $objDateTime = new \DateTime($raw, $this->timeZoneOffset);
    }
    else{
      $objDateTime = new \DateTimeImmutable($raw, $this->timeZoneOffset);
    }

    if($objDateTime->getTimezone() !== $this->timeZoneOffset){
      $objDateTime = $objDateTime->setTimezone($this->timeZoneOffset);
    }
    return $objDateTime;
  }

  public function convertTo(mixed $data): string|array|int|float|bool|null
  {
    if ($data === null && array_search('null', $this->types, true) !== false) {
      return null;
    }
    if (!$data instanceof \DateTimeInterface) {
      throw new \Exception('DateTimeConverter can not convert non-DateTimeInterface');
    }

    if($data->getTimezone() !== $this->timeZoneOffset){
      $data = \DateTimeImmutable::createFromInterface($data)->setTimezone($this->timeZoneOffset);
    }
    return TimeUtil::format($data, $this->useFraction);
  }
}
