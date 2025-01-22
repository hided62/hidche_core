<?php

namespace sammo\GameUnitConstraint;

use sammo\CityConst;
use sammo\General;

use function sammo\getCityLevelList;

class ReqCitiesWithCityLevel extends BaseGameUnitConstraint
{

    public readonly array $reqCities;
    public function __construct(
        public readonly int $reqCityLevel,
        ...$reqCities)
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
            if (!key_exists($cityID, $ownCities)) {
                continue;
            }

            if($ownCities[$cityID]['level'] >= $this->reqCityLevel) {
                return true;
            }
        }

        return false;
    }

    public function getInfo(): string
    {
        $cityLevelText = getCityLevelList()[$this->reqCityLevel];
        $cityNameText = implode(', ', $this->reqCities);
        return "{$cityNameText} {$cityLevelText}성 소유시 가능";
    }
}
