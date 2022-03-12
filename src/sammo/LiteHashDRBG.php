<?php

namespace sammo;

//NOTE: JavaScript 버전과 일치
const MAX_RNG_SUPPORT_BIT = 53;
if (PHP_INT_SIZE * 8 < MAX_RNG_SUPPORT_BIT) {
    throw new \RangeException("PHP not support {$MAX_RNG_SUPPORT_BIT} bit integer");
}

/**
 * Reseed를 하지 않는 단순한 형태의 sha512 drbg
 * float: bit를 강제로 채움
 **/


class LiteHashDRBG implements RNG
{
    const MAX_INT = (1 << MAX_RNG_SUPPORT_BIT) - 1;
    const BUFFER_BYTE_SIZE = 512 / 8; //SHA512

    protected string $buffer;
    protected int $bufferIdx;
    public function __construct(protected string $seed, protected int $stateIdx = 0, int $bufferIdx = 0)
    {
        if($bufferIdx < 0){
            throw new \InvalidArgumentException("bufferIdx {$bufferIdx} < 0");
        }
        if($bufferIdx >= self::BUFFER_BYTE_SIZE){
            throw new \InvalidArgumentException("bufferIdx {$bufferIdx} >= ".self::BUFFER_BYTE_SIZE);
        }
        if($stateIdx < 0){
            throw new \InvalidArgumentException("stateIdx {$stateIdx} < 0");
        }
        $this->genNextBlock();
        $this->bufferIdx = $bufferIdx;
    }

    protected function genNextBlock(): void
    {
        $hq = $this->seed . pack('V', $this->stateIdx);
        $this->buffer = hash('sha512', $hq, true);
        $this->bufferIdx = 0;
        $this->stateIdx += 1;
    }

    static function getMaxInt(): int
    {
        return self::MAX_INT;
    }

    const INT_BIT_MASK_MAP = [
        0x1 => 1,
        0x3 => 2,
        0x7 => 3,
        0xf => 4,
        0x1f => 5,
        0x3f => 6,
        0x7f => 7,
        0xff => 8,
        0x1ff => 9,
        0x3ff => 10,
        0x7ff => 11,
        0xfff => 12,
        0x1fff => 13,
        0x3fff => 14,
        0x7fff => 15,
        0xffff => 16,
        0x1ffff => 17,
        0x3ffff => 18,
        0x7ffff => 19,
        0xfffff => 20,
        0x1fffff => 21,
        0x3fffff => 22,
        0x7fffff => 23,
        0xffffff => 24,
        0x1ffffff => 25,
        0x3ffffff => 26,
        0x7ffffff => 27,
        0xfffffff => 28,
        0x1fffffff => 29,
        0x3fffffff => 30,
        0x7fffffff => 31,
        0xffffffff => 32,
        0x1ffffffff => 33,
        0x3ffffffff => 34,
        0x7ffffffff => 35,
        0xfffffffff => 36,
        0x1fffffffff => 37,
        0x3fffffffff => 38,
        0x7fffffffff => 39,
        0xffffffffff => 40,
        0x1ffffffffff => 41,
        0x3ffffffffff => 42,
        0x7ffffffffff => 43,
        0xfffffffffff => 44,
        0x1fffffffffff => 45,
        0x3fffffffffff => 46,
        0x7fffffffffff => 47,
        0xffffffffffff => 48,
        0x1ffffffffffff => 49,
        0x3ffffffffffff => 50,
        0x7ffffffffffff => 51,
        0xfffffffffffff => 52,
        0x1fffffffffffff => 53,
    ];

    private static function calcBitMask($n)
    {
        $n |= $n >> 1;
        $n |= $n >> 2;
        $n |= $n >> 4;
        $n |= $n >> 8;
        $n |= $n >> 16;
        $n |= $n >> 32;

        return $n;
    }


    public function nextBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            throw new \InvalidArgumentException("{$bytes} <= 0");
        }

        if ($this->bufferIdx + $bytes <= self::BUFFER_BYTE_SIZE) {
            $buffer = substr($this->buffer, $this->bufferIdx, $bytes);
            $this->bufferIdx += $bytes;
            if ($this->bufferIdx == self::BUFFER_BYTE_SIZE) {
                $this->genNextBlock();
            }
            return $buffer;
        }


        $result = [substr($this->buffer, $this->bufferIdx)];
        $remain = $bytes - (self::BUFFER_BYTE_SIZE - $this->bufferIdx);

        while ($remain > self::BUFFER_BYTE_SIZE) {
            $this->genNextBlock();
            $result[] = $this->buffer;
            $remain -= self::BUFFER_BYTE_SIZE;
        }

        $this->genNextBlock();
        if ($remain == 0) {
            return join("", $result);
        }

        $result[] = substr($this->buffer, 0, $remain);
        $this->bufferIdx = $remain;
        return join("", $result);
    }

    public function nextBits(int $bits): string
    {
        $bytes = ($bits + 7) >> 3;
        $headBits = $bits & 0x7;

        $buffer = $this->nextBytes($bytes);
        if ($headBits === 0) {
            return $buffer;
        }
        $buffer[$bytes - 1] = chr(ord($buffer[$bytes - 1]) & (0xff >> (8 - $headBits)));
        return $buffer;
    }


    static private function parseU64(string $value): int
    {
        return unpack('P', $value)[1];
    }

    private function _nextInt(int $bits): int
    {
        $buffer = $this->nextBits($bits) . "\x00\x00\x00\x00\x00\x00\x00";

        return self::parseU64($buffer);
    }

    public function nextInt(?int $max = null): int
    {
        if ($max === null || $max === self::MAX_INT) {
            $buffer = $this->nextBits(MAX_RNG_SUPPORT_BIT) . "\x00";
            return self::parseU64($buffer);
        }

        if ($max > self::MAX_INT) {
            throw new \InvalidArgumentException('Over Max Int');
        } else if ($max === 0) {
            return 0;
        } else if ($max < 0) {
            return -$this->nextInt(-$max);
        }

        $mask = self::calcBitMask($max);
        $bits = self::INT_BIT_MASK_MAP[$mask];

        $n = $this->_nextInt($bits);
        while ($n > $max) {
            $n = $this->_nextInt($bits);
        }

        return $n;
    }

    public function nextFloat1(): float
    {
        $max = 1 << MAX_RNG_SUPPORT_BIT;
        while (true) {
            $buffer = $this->nextBits(MAX_RNG_SUPPORT_BIT + 1) . "\x00";
            $nInt = self::parseU64($buffer);
            if ($nInt <= $max) {
                break;
            }
        }
        return $nInt / $max;
    }

    static function build(string $seed, int $idx = 0): self
    {
        return new LiteHashDRBG($seed, $idx);
    }
}
