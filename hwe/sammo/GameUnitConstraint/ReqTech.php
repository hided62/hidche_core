<?php

namespace sammo\GameUnitConstraint;

use sammo\General;
use sammo\KVStorage;

class ReqTech extends BaseGameUnitConstraint {

    public function __construct(public readonly int $reqTech)
    {
    }

    public function test(General $general, array $ownCities, array $ownRegions, int $relativeYear, int $tech, array $nationAux): bool
    {
        if ($tech < $this->reqTech) {
            return false;
        }
        return true;
    }

    public function getInfo(): string
    {
        return "기술력 {$this->reqTech} 이상 필요";
    }
}