<?php

namespace sammo\GameUnitConstraint;

use sammo\General;

class ReqMinRelYear extends BaseGameUnitConstraint {

    public function __construct(public readonly int $reqMinRelYear)
    {
    }

    public function test(General $general, array $ownCities, array $ownRegions, int $relativeYear, int $tech, array $nationAux): bool
    {
        return $relativeYear >= $this->reqMinRelYear;
    }

    public function getInfo(): string
    {
        return "{$this->reqMinRelYear}년 경과 후 사용 가능";
    }
}