<?php
namespace sammo\DTO\Converter;

interface Converter{
  public function __construct(array $types, ...$args);
  public function convertFrom(string|array|int|float|bool|null $raw): mixed;
  public function convertTo(mixed $data): string|array|int|float|bool|null;
}