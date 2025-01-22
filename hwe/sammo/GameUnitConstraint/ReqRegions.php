<?php

namespace sammo\GameUnitConstraint;

use sammo\CityConst;
use sammo\General;

class ReqRegions extends BaseGameUnitConstraint
{

    public readonly array $reqRegions;
    public function __construct(...$reqRegions)
    {
        $dstReqRegions = [];
        if (count($reqRegions) == 0) {
            $this->reqRegions = [];
            return;
        }

        foreach ($reqRegions as $region) {
            $dstReqRegions[CityConst::$regionMap[$region]] = $region;
        }

        $this->reqRegions = $dstReqRegions;
    }

    public function test(General $general, array $ownCities, array $ownRegions, int $relativeYear, int $tech, array $nationAux): bool
    {
        foreach ($this->reqRegions as $regionID => $regionText) {
            if (key_exists($regionID, $ownRegions)) {
                return true;
            }
        }

        return false;
    }

    public function getInfo(): string
    {
        $regionNameText = implode(', ', $this->reqRegions);
        return "{$regionNameText} 지역 소유시 가능";
    }
}
