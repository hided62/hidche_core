<?php

namespace sammo\GameUnitConstraint;

use sammo\General;

class ReqNotChief extends BaseGameUnitConstraint
{
    public function __construct()
    {
    }

    public function test(General $general, array $ownCities, array $ownRegions, int $relativeYear, int $tech, array $nationAux): bool
    {
        if($general->getVar('officer_level') < 5){
            return true;
        }

        return false;
    }

    public function getInfo(): string
    {
        return "군주 및 수뇌부는 불가";
    }
}
