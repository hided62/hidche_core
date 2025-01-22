<?php

namespace sammo\GameUnitConstraint;

use sammo\General;

use function sammo\getCityLevelList;

class ReqHighLevelCities extends BaseGameUnitConstraint
{

    public readonly array $reqCities;
    public function __construct(
        public readonly int $reqCityLevel,
        public readonly int $reqCityCount
    ) {}

    public function test(General $general, array $ownCities, array $ownRegions, int $relativeYear, int $tech, array $nationAux): bool
    {
        $cnt = 0;
        foreach ($ownCities as $cityItem) {
            if ($cityItem['level'] >= $this->reqCityLevel) {
                $cnt++;
            }
        }

        if($cnt >= $this->reqCityCount) {
            return true;
        }

        return false;
    }

    public function getInfo(): string
    {
        $cityLevelText = getCityLevelList()[$this->reqCityLevel];
        return "{$cityLevelText}성 {$this->reqCityCount}개 이상 소유시 가능";
    }
}
