<?php

namespace sammo\Event\Action;

use InvalidArgumentException;
use sammo\ActionLogger;
use sammo\CityConst;
use sammo\DB;
use sammo\GameConst;
use sammo\Json;
use sammo\KVStorage;
use sammo\Scenario\GeneralBuilder;
use sammo\Scenario\Nation;
use sammo\UniqueConst;
use sammo\Util;

use function sammo\GetNationColors;
use function sammo\pickGeneralFromPool;
use function sammo\refreshNationStaticInfo;

class RaiseNPCNation extends \sammo\Event\Action
{
    public function __construct()
    {
    }

    const CITY_KEYS = ['pop', 'agri', 'comm', 'secu', 'def', 'wall'];
    const MIN_DIST_USERNATION = 3;
    const MIN_DIST_NPCNATION = 2;

    private function calcAvgNationCity(array $cities)
    {
        if (count($cities) == 0) {
            throw new InvalidArgumentException();
        }

        $targetCities = [];

        foreach ($cities as $city) {
            if ($city['nation'] == 0) {
                continue;
            }
            $citySum = 0;
            foreach (static::CITY_KEYS as $key) {
                if ($key === 'pop') {
                    continue;
                }
                $citySum += $city[$key];
            }
            $city['sum'] = $citySum;

            $targetCities[] = $city;
        }

        $cityCnt = count($targetCities);

        if ($cityCnt == 0) {
            $target = Util::choiceRandom($cities);
            $randCity = [];
            foreach (static::CITY_KEYS as $key) {
                $randCity[$key] = $target["{$key}_max"];
            }
            return $randCity;
        }

        usort($targetCities, function (array $lhs, array $rhs) {
            return $lhs['sum'] <=> $rhs['sum'];
        });


        //최소, 최대 도시 몇개를 뺀다. 정렬은 신경쓰지 않는다.

        if ($cityCnt >= 3) {
            $reduceCnt = Util::valueFit(Util::round($cityCnt / 6, 0), 1);
            foreach (Util::range($reduceCnt) as $_idx) {
                array_pop($targetCities);
            }

            foreach (Util::range($reduceCnt) as $idx) {
                $targetCities[$idx] = array_pop($targetCities);
            }

            $cityCnt -= $reduceCnt * 2;
        }

        $avgCity = [];
        foreach ($targetCities as $city) {
            foreach (static::CITY_KEYS as $key) {
                $avgCity[$key] = ($avgCity[$key] ?? 0) + $city[$key];
            }
        }

        foreach (static::CITY_KEYS as $key) {
            $avgCity[$key] = Util::toInt($avgCity[$key] / $cityCnt);
        }
        return $avgCity;
    }

    public function calcAvgNationGeneralCnt(): int
    {
        $nationUsers = [];
        foreach (\sammo\getAllNationStaticInfo() as $nation) {
            if ($nation['level'] == 0) {
                continue;
            }
            $nationUsers[] = $nation['gennum'];
        }

        $nationCnt = count($nationUsers);
        if ($nationCnt == 0) {
            return GameConst::$initialNationGenLimit;
        }

        sort($nationUsers);
        if ($nationCnt >= 3) {
            $reduceCnt = Util::valueFit(Util::round($nationCnt / 6, 0), 1);
            foreach (Util::range($reduceCnt) as $_idx) {
                array_pop($nationUsers);
            }

            foreach (Util::range($reduceCnt) as $idx) {
                $nationUsers[$idx] = array_pop($nationUsers);
            }
            $nationCnt -= $reduceCnt * 2;
        }

        return Util::round(array_sum($nationUsers) / $nationCnt);
    }

    public function calcAvgTech(): int
    {
        $techSum = 0;
        $nationCnt = 0;
        foreach (\sammo\getAllNationStaticInfo() as $nation) {
            if ($nation['level'] == 0) {
                continue;
            }
            $techSum += $nation['tech'];
            $nationCnt += 1;
        }
        if ($nationCnt == 0) {
            return 0;
        }
        return Util::toInt($techSum / $nationCnt);
    }

    private function buildNation(int $nationID, int $tech, array $baseCity, array $avgCity, int $genCnt, $env)
    {
        $db = DB::db();

        $targetCity = [
            'trust' => 100
        ];
        foreach (static::CITY_KEYS as $key) {
            $targetCity[$key] = min($baseCity["{$key}_max"], $avgCity[$key]);
        }

        $pickTypeList = ['무' => 1, '지' => 1];

        $cityID = $baseCity['city'];
        $cityName = $baseCity['name'];
        $nationName = "ⓤ{$cityName}";

        $color = Util::choiceRandom(GetNationColors());
        $nationObj = new Nation($nationID, $nationName, $color, 0, 2000, "우리도 할 수 있다! {$cityName}군", $tech, null, 2, [$cityName]);
        $nationObj->build($env);

        $ruler = (new GeneralBuilder("{$cityName}태수", false, null, $nationID))
            ->setOfficerLevel(12)
            ->setCityID($cityID)
            ->setNPCType(6)
            ->setGoldRice(1000, 1000)
            ->fillRandomStat($pickTypeList)
            ->setKillturn(240)
            ->fillRemainSpecAsZero($env);
        $ruler->build($env);

        $nationObj->addGeneral($ruler);

        $birthYear = $env['year'] - 20;
        $deadYearMin = $env['year'] + 10;

        foreach (pickGeneralFromPool(DB::db(), 0, $genCnt - 1) as $pickedNPC) {
            //대충 10년후부터 6년마다 절반?
            $deadYear = $deadYearMin + Util::toInt(60 * (1 - log(Util::randRange(1, 1024), 2)/10));
            $newNPC = $pickedNPC->getGeneralBuilder();
            $newNPC->setNationID($nationID)
                ->setCityID($cityID)
                ->setNPCType(6)
                ->setGoldRice(1000, 1000)
                ->setLifeSpan($birthYear, $deadYear)
                ->fillRandomStat($pickTypeList)
                ->fillRemainSpecAsZero($env);
            $newNPC->build($env);
            $pickedNPC->occupyGeneralName();

            $nationObj->addGeneral($newNPC);
        }

        $nationObj->postBuild($env);
        $db->update('city', $targetCity, 'city = %i', $cityID);
        refreshNationStaticInfo();
    }

    public function run(array $env)
    {
        $db = DB::db();

        $allCities = $db->query('SELECT * FROM city WHERE 5 <= level AND level <= 6'); //소, 중 성만 선택

        $emptyCities = [];
        $occupiedCities = [];
        $npcCities = [];

        $avgCity = $this->calcAvgNationCity($allCities);

        foreach ($allCities as $city) {
            if ($city['nation'] == 0) {
                $emptyCities[$city['city']] = $city;
            } else {
                $occupiedCities[$city['city']] = $city;
            }
        }

        $avgGenCnt = $this->calcAvgNationGeneralCnt();

        $serverID = UniqueConst::$serverID;
        $lastNationID = max(
            $db->queryFirstField("SELECT max(`nation`) FROM `nation`"),
            $db->queryFirstField("SELECT max(`nation`) FROM `ng_old_nations` WHERE server_id = %s", $serverID),
        );

        $avgTech = $this->calcAvgTech();
        Util::shuffle_assoc($emptyCities);

        $occupiedCitiesID = array_keys($occupiedCities);
        $npcCitiesID = [];

        refreshNationStaticInfo();

        foreach ($emptyCities as $emptyCity) {
            $cityID = $emptyCity['city'];

            $minDistance = 999;
            foreach ($occupiedCitiesID as $targetCityID) {
                $minDistance = min(\sammo\calcCityDistance($cityID, $targetCityID, null), $minDistance);
                if ($minDistance < static::MIN_DIST_USERNATION) {
                    break;
                }
            }
            if ($minDistance < static::MIN_DIST_USERNATION) {
                continue;
            }

            $minDistance = 999;
            foreach ($npcCitiesID as $targetCityID) {
                $minDistance = min(\sammo\calcCityDistance($cityID, $targetCityID, null), $minDistance);
                if ($minDistance < static::MIN_DIST_NPCNATION) {
                    break;
                }
            }
            if ($minDistance < static::MIN_DIST_NPCNATION) {
                continue;
            }

            //TODO: 거리 측정
            $lastNationID += 1;
            $this->buildNation($lastNationID, $avgTech, $emptyCity, $avgCity, $avgGenCnt, $env);
            $npcCities[$cityID] = $emptyCity;
            $npcCitiesID[] = $cityID;
        }

        if (count($npcCities) > 0) {
            $logger = new ActionLogger(0, 0, $env['year'], $env['month']);
            $logger->pushGlobalHistoryLog("<L><b>【공지】</b></>공백지에 임의의 국가가 생성되었습니다.");
            $logger->flush();
        }

        return [__CLASS__, count($npcCities)];
    }
}
