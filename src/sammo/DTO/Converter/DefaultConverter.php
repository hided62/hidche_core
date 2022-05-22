<?php

namespace sammo\DTO\Converter;

use sammo\DTO\DTO;

class DefaultConverter implements Converter
{
  public function __construct(private array $types, ...$args)
  {
  }

  public static function convertFromItem(string $type, $raw, bool &$success): mixed
  {
    $success = false;
    if (is_subclass_of($type, \UnitEnum::class)) {
      $enumType = new \ReflectionEnum($type);
      if ($enumType->isBacked()) {
        $enum = $enumType->getMethod('tryFrom')->invoke(null, $raw);
        if ($enum === null) {
          return null;
        }

        $success = true;
        return $enum;
      }

      if (!$enumType->hasCase($raw)) {
        return null;
      }
      $success = true;
      return $enumType->getCase($raw)->getValue();
    }

    if (is_subclass_of($type, DTO::class)) {
      try {
        $class = new \ReflectionClass($type);
        $obj = $class->getMethod('fromArray')->invoke(null, $raw);
        $success = true;
        return $obj;
      } catch (\Throwable) {
      }
      return null;
    }

    if ($type === 'array') {
      if (!is_array($raw)) {
        return null;
      }
      if (!array_is_list($raw)) {
        throw new \Exception('value is not a array');
      }
      foreach ($raw as $value) {
        if (is_int($value) || is_float($value) || is_string($value)) {
          continue;
        }
        if (is_bool($value) || is_null($value)) {
          continue;
        }
        throw new \Exception('DefaultConverter can not convert array');
      }
      $success = true;
      return $raw;
    }

    if($type === 'int' && is_int($raw)){
      $success = true;
      return $raw;
    }

    if($type === 'float' && is_float($raw)){
      $success = true;
      return $raw;
    }

    if($type === 'string' && is_string($raw)){
      $success = true;
      return $raw;
    }

    if($type === 'bool' && is_bool($raw)){
      $success = true;
      return $raw;
    }

    return null;
  }

  public function convertFrom(string|array|int|float|bool|null $raw): mixed
  {
    if ($raw === null && array_search('null', $this->types, true) !== false) {
      return null;
    }

    foreach ($this->types as $type) {
      $success = false;
      $value = self::convertFromItem($type, $raw, $success);
      if ($success) {
        return $value;
      }
    }

    throw new \Exception('DefaultConverter can not convert');
  }

  public function convertTo(mixed $data): string|array|int|float|bool|null
  {
    if ($data === null) {
      return $data;
    }

    if ($data instanceof \UnitEnum) {
      if ($data instanceof \BackedEnum) {
        return $data->value;
      }
      return $data->name;
    }

    if ($data instanceof DTO) {
      return $data->toArray();
    }

    if (is_array($data)) {
      if (!array_is_list($data)) {
        throw new \Exception('value is not a array');
      }
      foreach ($data as $value) {
        if (is_int($value) || is_float($value) || is_string($value)) {
          continue;
        }
        if (is_bool($value) || is_null($value)) {
          continue;
        }
        throw new \Exception('DefaultConverter can not convert array');
      }
    }

    return $data;
  }
}
