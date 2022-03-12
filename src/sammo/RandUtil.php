<?php

namespace sammo;

class RandUtil
{
    public function __construct(protected RNG $rng)
    {
    }

    public function nextFloat1(): float
    {
        return $this->rng->nextFloat1();
    }

    public function nextRange(int|float $min, int|float $max): float
    {
        $range = $max - $min;
        return $this->nextFloat1() * $range + $min;
    }

    public function nextRangeInt(int $min, int $max): int
    {
        $range = $max - $min;
        if ($range > $this->rng->getMaxInt()) {
            throw new \InvalidArgumentException("Invalid random int range");
        }
        return $this->rng->nextInt($range) + $min;
    }

    public function nextInt(?int $max = null): int{
        return $this->rng->nextInt($max);
    }

    public function nextBit(): bool
    {
        return $this->rng->nextBits(1) !== "\0";
    }

    public function nextBool(int|float $prob = 0.5): bool
    {
        if ($prob >= 1) {
            return true;
        }
        return $this->nextFloat1() < $prob;
    }

    public function shuffle(array $srcArray): array
    {
        if(!$srcArray){
            return $srcArray;
        }
        $cnt = count($srcArray);
        if ($cnt > $this->rng->getMaxInt()) {
            throw new \InvalidArgumentException("Invalid random int range");
        }
        $result = [];
        foreach ($srcArray as $val) {
            $result[] = $val;
        }

        //PHP의 range는 max 포함
        foreach (range(0, $cnt - 1) as $srcIdx) {
            $destIdx = $this->rng->nextInt($cnt - $srcIdx - 1) + $srcIdx;
            if($srcIdx === $destIdx){
                continue;
            }
            $tmpVal = $result[$srcIdx];
            $result[$srcIdx] = $result[$destIdx];
            $result[$destIdx] = $tmpVal;
        }

        return $result;
    }

    public function shuffleAssoc(array $srcArray): array
    {
        if(!$srcArray){
            return $srcArray;
        }
        $result = [];
        foreach ($this->shuffle(array_keys($srcArray)) as $key) {
            $result[$key] = $srcArray[$key];
        }
        return $result;
    }

    public function choice(array $items)
    {
        $keys = array_keys($items);
        $keyIdx = $this->rng->nextInt(count($keys) - 1);
        return $items[$keys[$keyIdx]];
    }

    public function choiceUsingWeight(array $items)
    {
        $sum = 0;
        foreach ($items as $value) {
            if ($value <= 0) {
                continue;
            }
            $sum += $value;
        }

        $rd = $this->nextFloat1() * $sum;
        foreach ($items as $item => $value) {
            if ($value <= 0) {
                $value = 0;
            }
            if ($rd <= $value) {
                return $item;
            }
            $rd -= $value;
        }

        //fallback. 이곳으로 빠지지 않음
        end($items);
        return $items[key($items)][0];
    }

    public function choiceUsingWeightPair(array $items)
    {
        $sum = 0;
        foreach ($items as [$item, $value]) {
            if ($value <= 0) {
                continue;
            }
            $sum += $value;
        }

        $rd = $this->nextFloat1() * $sum;
        foreach ($items as [$item, $value]) {
            if ($value <= 0) {
                $value = 0;
            }
            if ($rd <= $value) {
                return $item;
            }
            $rd -= $value;
        }

        //fallback. 이곳으로 빠지지 않음
        end($items);
        return $items[key($items)][0];
    }
}
