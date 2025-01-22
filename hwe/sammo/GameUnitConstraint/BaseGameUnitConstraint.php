<?php

namespace sammo\GameUnitConstraint;

use sammo\General;

abstract class BaseGameUnitConstraint {
    public abstract function test(General $general, array $ownCities, array $ownRegions, int $relativeYear, int $tech, array $nationAux): bool;

    public abstract function getInfo(): string;
}