<?php

namespace sammo\GameUnitConstraint;

use sammo\CityConst;
use sammo\General;

class ReqCities extends BaseGameUnitConstraint
{

    public readonly array $reqCities;
    public function __construct(...$reqCities)
    {
        $dstReqCities = [];
        if (count($reqCities) == 0) {
            $this->reqCities = [];
            return;
        }

        foreach ($reqCities as $city) {
            $dstReqCities[CityConst::byName($city)->id] = $city;
        }
        $this->reqCities = $dstReqCities;
    }

    public function test(General $general, array $ownCities, array $ownRegions, int $relativeYear, int $tech, array $nationAux): bool
    {
        foreach ($this->reqCities as $cityID => $cityName) {
            if (key_exists($cityID, $ownCities)) {
                return true;
            }
        }

        return false;
    }

    public function getInfo(): string
    {
        $cityNameText = implode(', ', $this->reqCities);
        return "{$cityNameText} 소유시 가능";
    }
}
