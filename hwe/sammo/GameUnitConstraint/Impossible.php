<?php

namespace sammo\GameUnitConstraint;

use sammo\General;

class Impossible extends BaseGameUnitConstraint {

    public function test(General $general, array $ownCities, array $ownRegions, int $relativeYear, int $tech, array $nationAux): bool
    {
        return false;
    }

    public function getInfo(): string
    {
        return "불가능";
    }
}