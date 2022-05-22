<?php

namespace sammo\DTO;

use sammo\DTO\Attr\Convert;
use sammo\DTO\Converter\DefaultConverter;
use sammo\Json;

abstract class DTO
{
  public static function fromArray(\ArrayAccess|array $array): static
  {
    $reflection = new \ReflectionClass(static::class);
    $args = [];

    if ($array instanceof \ArrayAccess) {
      $keyExists = fn (string|int $key) => $array->offsetExists($key);
    } else {
      $keyExists = fn (string|int $key) => array_key_exists($key, $array);
    }

    foreach ($reflection->getProperties(
      \ReflectionProperty::IS_PUBLIC
    ) as $property) {
      $attrs = Util\Util::getAttrs($property);
      $name = $property->getName();
      $rawName = $name;

      if (key_exists(Attr\RawName::class, $attrs)) {
        $rawAttr = $attrs[Attr\RawName::class];
        $attr = new Attr\RawName(...$rawAttr->getArguments());
        $rawName = $attr->rawName;
      }

      if (!$keyExists($rawName)) {
        if ($property->hasDefaultValue()) {
          $args[$name] = $property->getDefaultValue();
        } else if ($property->getType()->allowsNull()) {
          $args[$name] = null;
        } else {
          throw new \Exception("Missing property: {$name}");
        }
        continue;
      }

      $value = $array[$rawName];

      if (key_exists(Attr\JsonString::class, $attrs)) {
        $rawAttr = $attrs[Attr\JsonString::class];
        $attr = new Attr\JsonString(...$rawAttr->getArguments());
        $value = Json::decode($value);
      }

      $propTypes = Util\Util::getPropTypes($property);
      if (key_exists(Attr\Convert::class, $attrs)) {
        $rawAttr = $attrs[Attr\Convert::class];
        $converter = new Convert($propTypes, ...$rawAttr->getArguments());
      } else {
        $converter = new Convert($propTypes, DefaultConverter::class);
      }
      $value = $converter->convertFrom($value);

      $args[$name] = $value;
    }

    $object = $reflection->newInstanceArgs($args);
    return $object;
  }

  public function toArray(): array
  {
    $reflection = new \ReflectionClass($this::class);
    $result = [];
    foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
      $value = $property->getValue($this);
      $attrs = Util\Util::getAttrs($property);
      $name = $property->getName();

      if (key_exists(Attr\RawName::class, $attrs)) {
        $rawAttr = $attrs[Attr\RawName::class];
        $attr = new Attr\RawName(...$rawAttr->getArguments());
        $name = $attr->rawName;
      }

      $propTypes = Util\Util::getPropTypes($property);
      if (key_exists(Attr\Convert::class, $attrs)) {
        $converter = new Convert($propTypes, ...$attrs[Attr\Convert::class]->getArguments());
      } else {
        $converter = new Convert($propTypes, DefaultConverter::class);
      }
      $value = $converter->convertTo($value);

      if (key_exists(Attr\JsonString::class, $attrs)) {
        $rawAttr = $attrs[Attr\JsonString::class];
        $attr = new Attr\JsonString(...$rawAttr->getArguments());
        $value = Json::encode($value, $attr->emptyItemIsArray ? JSON::EMPTY_ARRAY_IS_DICT : 0);
      }

      if ($value === null && key_exists(Attr\NullIsUndefined::class, $attrs)) {
        continue;
      }
      $result[$name] = $value;
    }
    return $result;
  }
}
