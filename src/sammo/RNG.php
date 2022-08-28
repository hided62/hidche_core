<?php
namespace sammo;

interface RNG
{

    /**
     * @return int nextInt()가 반환 가능한 최댓값
     */
    public static function getMaxInt(): int;

    /**
     *
     * @param int $bytes
     * @return string Little Endian 형태로 채워진 binary 값
     */
    public function nextBytes(int $bytes): string;
    public function nextBits(int $bits): string;

    /**
     * @param ?int $max 최대치(해당 값 포함)
     * @return int 0과 최대치 사이의 임의의 정수
     */
    public function nextInt(?int $max = null): int;

    public function nextFloat1(): float;

}
