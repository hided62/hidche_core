<?php

namespace sammo;

use Ds\Map;
use sammo\Enums\GeneralAccessLogColumn;
use sammo\Enums\GeneralLiteQueryMode;
use sammo\Enums\GeneralQueryMode;
use sammo\Enums\RankColumn;

abstract class GeneralBase
{
    use LazyVarUpdater;

    protected $raw = [];
    protected $rawCity = null;

    /** @var Map<RankColumn,int> */
    protected Map $rankVarRead;

    /** @var \sammo\ActionLogger */
    protected $logger;

    const TURNTIME_FULL_MS = -1;
    const TURNTIME_FULL = 0;
    const TURNTIME_HMS = 1;
    const TURNTIME_HM = 2;

    protected static $prohibitedDirectUpdateVars = [
        //Reason: iAction
        'leadership' => 1,
        'power' => 1,
        'intel' => 1,
        'nation' => 2,
        'officer_level' => 1,
        //NOTE: officerLevelObj로 인해 국가의 '레벨'이 바뀌는 것도 조심해야 하나, 국가 레벨의 변경은 월 초/말에만 일어남.
        'special' => 1,
        'special2' => 1,
        'personal' => 1,
        'horse' => 1,
        'weapon' => 1,
        'book' => 1,
        'item' => 1
    ];

    function initLogger(int $year, int $month)
    {
        $this->logger = new ActionLogger(
            $this->getVar('no'),
            $this->getVar('nation'),
            $year,
            $month,
            false
        );
    }

    function getTurnTime(int $short = self::TURNTIME_FULL_MS): ?string
    {
        if(!key_exists('turntime', $this->raw)){
            return null;
        }

        return [
            self::TURNTIME_FULL_MS => function ($turntime) {
                return $turntime;
            },
            self::TURNTIME_FULL => function ($turntime) {
                return substr($turntime, 0, 19);
            },
            self::TURNTIME_HMS => function ($turntime) {
                return substr($turntime, 11, 8);
            },
            self::TURNTIME_HM => function ($turntime) {
                return substr($turntime, 11, 5);
            },
        ][$short]($this->getVar('turntime'));
    }

    function getNPCType(): int
    {
        return $this->raw['npc'];
    }

    function getName(): string
    {
        return $this->raw['name'];
    }

    function getID(): int
    {
        return $this->raw['no'];
    }

    function getRawCity(): ?array
    {
        return $this->rawCity;
    }

    function setRawCity(?array $city)
    {
        $this->rawCity = $city;
    }

    function getCityID(): int
    {
        return $this->raw['city'];
    }

    function getNationID(): int
    {
        return $this->raw['nation'];
    }

    function getStaticNation(): array
    {
        return getNationStaticInfo($this->raw['nation']);
    }

    function getLogger(): ?ActionLogger
    {
        return $this->logger;
    }

    function getDex(GameUnitDetail $crewType)
    {
        $armType = $crewType->armType;

        if ($armType == GameUnitConst::T_CASTLE) {
            $armType = GameUnitConst::T_SIEGE;
        }

        return $this->getVar("dex{$armType}");
    }

    function getRankVar(RankColumn $key, $defaultValue = null): int
    {
        if (!$this->rankVarRead->hasKey($key)) {
            if ($defaultValue === null) {
                throw new \RuntimeException('인자가 없음 : ' . $key->value);
            }
            return $defaultValue;
        }

        return $this->rankVarRead[$key];
    }

    abstract function applyDB($db): bool;

    static public function mergeQueryColumn(?array $reqColumns = null, GeneralQueryMode|GeneralLiteQueryMode $queryMode = GeneralQueryMode::Full): array
    {
        $minimumColumn = ['no', 'name', 'owner', 'npc', 'city', 'nation', 'officer_level', 'officer_city'];
        $defaultEventColumn = [
            'no', 'name', 'npc', 'owner', 'city', 'nation', 'officer_level', 'officer_city',
            'special', 'special2', 'personal',
            'horse', 'weapon', 'book', 'item', 'last_turn', 'aux', 'turntime',
        ];
        $fullColumn = [
            'no', 'name', 'owner', 'owner_name', 'picture', 'imgsvr', 'nation', 'city', 'troop', 'injury', 'affinity',
            'leadership', 'leadership_exp', 'strength', 'strength_exp', 'intel', 'intel_exp', 'weapon', 'book', 'horse', 'item',
            'experience', 'dedication', 'officer_level', 'officer_city', 'gold', 'rice', 'crew', 'crewtype', 'train', 'atmos', 'turntime',
            'makelimit', 'killturn', 'block', 'dedlevel', 'explevel', 'age', 'startage', 'belong',
            'personal', 'special', 'special2', 'defence_train', 'tnmt', 'npc', 'npc_org', 'deadyear', 'npcmsg',
            'dex1', 'dex2', 'dex3', 'dex4', 'dex5', 'betray',
            'recent_war', 'last_turn', 'myset',
            'specage', 'specage2', 'aux', 'permission', 'penalty',
        ];
        $fullAcessLogColumn = [
            GeneralAccessLogColumn::refreshScore,
            GeneralAccessLogColumn::refreshScoreTotal,
        ];

        if ($reqColumns === null) {
            switch ($queryMode) {
                case GeneralLiteQueryMode::Core:
                    return [$minimumColumn, [], []];
                case GeneralLiteQueryMode::Lite:
                    return [$defaultEventColumn, [], []];
                case GeneralLiteQueryMode::Full:
                case GeneralQueryMode::Full:
                    return [$fullColumn, RankColumn::cases(), []];
                case GeneralQueryMode::FullWithAccessLog:
                    return [$fullColumn, RankColumn::cases(), $fullAcessLogColumn];
            }
        }

        /** @var RankColumn[] */
        $rankColumn = [];
        $subColumn = [];
        $accessLogColumn = [];
        foreach ($reqColumns as $column) {
            if ($column instanceof RankColumn) {
                $rankColumn[] = $column;
                continue;
            }
            if ($column instanceof GeneralAccessLogColumn) {
                $accessLogColumn[] = $column;
                continue;
            }


            $enumKey = RankColumn::tryFrom($column);
            if ($enumKey !== null) {
                $rankColumn[] = $enumKey;
                continue;
            }
            $enumKey = GeneralAccessLogColumn::tryFrom($column);
            if ($enumKey !== null) {
                $accessLogColumn[] = $enumKey;
                continue;
            }
            $subColumn[] = $column;
        }

        switch ($queryMode) {
            case GeneralLiteQueryMode::Core:
                return [array_unique(array_merge($minimumColumn, $subColumn)), $rankColumn, $accessLogColumn];
            case GeneralLiteQueryMode::Lite:
                return [array_unique(array_merge($defaultEventColumn, $subColumn)), $rankColumn, $accessLogColumn];
            case GeneralLiteQueryMode::Full:
            case GeneralQueryMode::Full:
                return [array_unique(array_merge($fullColumn, $subColumn)), $rankColumn, $accessLogColumn];
            case GeneralQueryMode::FullWithAccessLog:
                return [array_unique(array_merge($fullColumn, $subColumn)), $rankColumn, array_unique(array_merge($fullAcessLogColumn, $accessLogColumn))];
            default:
                 throw new \RuntimeException('invalid query mode');
        }
    }

}
