<?php

namespace sammo;


/**
 * 내부에 랜덤 값을 호출하지 않을 것이 확실할 때 RNG 대용으로 사용
 * 어떤 함수를 호출하든 에러 발생
 */
class NoRNG implements RNG
{
  const MAX_RNG_SUPPORT_BIT = 53;
  const MAX_INT = (1 << self::MAX_RNG_SUPPORT_BIT) - 1;
  public function __construct()
  {
  }

  static function getMaxInt(): int
  {
    return self::MAX_INT;
  }

  public function nextBytes(int $bytes): string
  {
    throw new MustNotBeReachedException();
  }

  public function nextBits(int $bits): string
  {
    throw new MustNotBeReachedException();
  }

  public function nextInt(?int $max = null): int
  {
    throw new MustNotBeReachedException();
  }

  public function nextFloat1(): float
  {
    throw new MustNotBeReachedException();
  }

  private static RandUtil|null $instance = null;
  static function rngInstance(): RandUtil
  {
    if (self::$instance === null) {
      self::$instance = new RandUtil(new NoRNG());
    }
    return self::$instance;
  }
}
