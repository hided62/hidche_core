<?php

namespace sammo;

use Ds\Map;
use sammo\Enums\GeneralLiteQueryMode;
use sammo\Enums\RankColumn;

class GeneralLite extends GeneralBase
{
    /**
     * @param array $raw DB row값.
     * @param null|array $city DB city 테이블의 row값
     * @param int|null $year 게임 연도
     * @param int|null $month 게임 월
     */
    public function __construct(array $raw, ?Map $rawRank, ?array $city, ?array $nation, ?int $year, ?int $month)
    {
        if ($nation === null) {
            $nation = getNationStaticInfo($raw['nation']);
        }

        $this->raw = $raw;
        $this->rawCity = $city;

        if ($year !== null && $month !== null) {
            $this->initLogger($year, $month);
        }

        if ($rawRank) {
            $this->rankVarRead = $rawRank;
        } else {
            $this->rankVarRead = new Map();
        }
    }

    /**
     * @param \MeekroDB $db
     */
    function applyDB($db): bool
    {
        $updateVals = $this->getUpdatedValues();


        $generalID = $this->getID();
        $result = false;

        if ($updateVals) {
            $db->update('general', $updateVals, 'no=%i', $generalID);
            $result = $result || $db->affectedRows() > 0;
            if (key_exists('nation', $updateVals)) {
                $db->update('rank_data', [
                    'nation_id' => $updateVals['nation']
                ], 'general_id = %i', $generalID);
                $result = true;
            }
            $this->flushUpdateValues();
        }

        $this->getLogger()->flush();
        return $result;
    }

    /**
     * @param ?int[] $generalIDList
     * @param null|array<string|RankColumn> $column
     * @param GeneralLiteQueryMode $queryMode
     * @return \sammo\GeneralLite[]
     * @throws MustNotBeReachedException
     */
    static public function createObjListFromDB(?array $generalIDList, ?array $column = null, GeneralLiteQueryMode $queryMode = GeneralLiteQueryMode::Full): array
    {
        if ($generalIDList === []) {
            return [];
        }

        if($queryMode > GeneralLiteQueryMode::Full){
            throw new \InvalidArgumentException('지원하지 않는 queryMode:' . $queryMode->name);
        }

        $db = DB::db();
        if ($queryMode->value > 0) {
            $gameStor = KVStorage::getStorage($db, 'game_env');
            [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
        } else {
            $year = null;
            $month = null;
        }

        /**
         * @var string[] $column
         */
        [$column, $rankColumn,] = static::mergeQueryColumn($column, $queryMode);

        if ($generalIDList === null) {
            $rawGenerals = Util::convertArrayToDict(
                $db->query('SELECT %l FROM general WHERE 1', Util::formatListOfBackticks($column)),
                'no'
            );

            $generalIDList = array_keys($rawGenerals);
        } else {
            $rawGenerals = Util::convertArrayToDict(
                $db->query('SELECT %l FROM general WHERE no IN %li', Util::formatListOfBackticks($column), $generalIDList),
                'no'
            );
        }

        /** @var Map<int,Map<RankColumn,int|float>> */
        $rawRanks = new Map();
        if ($rankColumn) {
            $rawValue = $db->queryAllLists(
                'SELECT `general_id`, `type`, `value` FROM rank_data WHERE general_id IN %li AND `type` IN %ls',
                $generalIDList,
                array_map(fn (\BackedEnum $e) => $e->value, $rankColumn)
            );
            foreach ($rawValue as [$generalID, $rawRankType, $rankValue]) {
                if (!$rawRanks->hasKey($generalID)) {
                    $rawRanks[$generalID] = new Map();
                }

                $rankType = RankColumn::from($rawRankType);
                $rawRanks[$generalID][$rankType] = $rankValue;
            }
        }

        $result = [];
        foreach ($generalIDList as $generalID) {
            if (!key_exists($generalID, $rawGenerals)) {
                $result[$generalID] = new DummyGeneral($queryMode->value > 0);
                continue;
            }
            $result[$generalID] = new static($rawGenerals[$generalID], $rawRanks[$generalID] ?? null, null, null, $year, $month);
        }

        return $result;
    }

    static public function createObjFromDB(int $generalID, ?array $column = null, GeneralLiteQueryMode $queryMode = GeneralLiteQueryMode::Full): self
    {
        $db = DB::db();
        if ($queryMode->value > 0) {
            $gameStor = KVStorage::getStorage($db, 'game_env');
            [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
        } else {
            $year = null;
            $month = null;
        }

        /**
         * @var string[] $column
         * @var RankColumn[] $rankColumn
         */
        [$column, $rankColumn,] = static::mergeQueryColumn($column, $queryMode);

        $rawGeneral = $db->queryFirstRow('SELECT %l FROM general WHERE no = %i', Util::formatListOfBackticks($column), $generalID);

        if (!$rawGeneral) {
            return new DummyGeneralLite($queryMode->value > 0);
        }

        $rawRankValues = new Map();
        if ($rankColumn) {
            $rawValue = $db->queryAllLists(
                'SELECT `type`, `value` FROM rank_data WHERE general_id = %i AND `type` IN %ls',
                $generalID,
                array_map(fn (\BackedEnum $e) => $e->value, $rankColumn)
            );
            foreach ($rawValue as [$rawRankType, $rankValue]) {
                $rankType = RankColumn::tryFrom($rawRankType);
                $rawRankValues->put($rankType, $rankValue);
            }
        }

        $general = new static($rawGeneral, $rawRankValues, null, null, $year, $month);

        return $general;
    }
}
