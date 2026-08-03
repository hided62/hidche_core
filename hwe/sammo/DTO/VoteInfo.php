<?php

namespace sammo\DTO;

use sammo\GameClock;
use sammo\Util;

class VoteInfo extends \LDTO\DTO
{
  /**
   * 기존 문자열만 가진 vote도 읽되, 저장 경계에서는 반드시 tick을 함께 둡니다.
   *
   * @return array<string,mixed>
   */
  public static function normalizeGameStorage(array $raw, GameClock $clock): array
  {
    if (!array_key_exists('startTick', $raw)) {
      $raw['startTick'] = $clock->dateTimeToTick(new \DateTimeImmutable((string)$raw['startDate']));
    }
    if (!array_key_exists('endTick', $raw)) {
      $raw['endTick'] = ($raw['endDate'] ?? null) === null
        ? null
        : $clock->dateTimeToTick(new \DateTimeImmutable((string)$raw['endDate']));
    }

    $raw['startTick'] = Util::toInt($raw['startTick']);
    $raw['endTick'] = $raw['endTick'] === null ? null : Util::toInt($raw['endTick']);
    $raw['startDate'] = $clock->formatTick($raw['startTick']);
    $raw['endDate'] = $raw['endTick'] === null ? null : $clock->formatTick($raw['endTick']);
    return $raw;
  }

  public static function fromGameStorage(array $raw, GameClock $clock): self
  {
    $raw = self::normalizeGameStorage($raw, $clock);
    unset($raw['startTick'], $raw['endTick']);
    return self::fromArray($raw);
  }

  public function __construct(
    public int $id,
    public string $title,
    public int $multipleOptions,
    public ?string $opener,

    public string $startDate,
    public ?string $endDate,

    /** @var string[] */
    public array $options,
  ) {
  }
}
