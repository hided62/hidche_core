<?php

namespace sammo\GeneralPool;

use sammo\AbsFromUserPool;
use sammo\CentennialAllStarGrowthService;
use sammo\Json;
use sammo\RandUtil;

class SPoolUnderU100 extends AbsFromUserPool
{
    private const MIN_DEX_WEIGHT = 100000;
    private const STAT_BONUS_MIN_TOTAL = 160;
    private const STAT_BONUS_MAX_TOTAL = 190;
    private const STAT_BONUS_MAX_MULTIPLIER = 1.5;

    public function __construct(\MeekroDB $db, RandUtil $rng, array $info, int $validUntil)
    {
        $targetInfo = $info;
        $initialInfo = $info;
        foreach ([
            'leadership',
            'strength',
            'intel',
            'experience',
            'dedication',
            'dex',
            'specialDomestic',
            'specialWar',
        ] as $targetKey) {
            unset($initialInfo[$targetKey]);
        }

        parent::__construct($db, $rng, $initialInfo, $validUntil);
        $this->info = $targetInfo;
        CentennialAllStarGrowthService::attachInitialTarget($this->builder, $targetInfo);
    }

    public static function getPoolName(): string
    {
        return '100기 올스타 클래식';
    }

    protected static function getCandidateWeight(array $info, int $owner): int|float
    {
        $dexWeight = max(
            self::MIN_DEX_WEIGHT,
            array_sum($info['dex'] ?? [])
        );
        if ($owner <= 0) {
            return $dexWeight;
        }

        $statTotal = array_sum([
            (int) ($info['leadership'] ?? 0),
            (int) ($info['strength'] ?? 0),
            (int) ($info['intel'] ?? 0),
        ]);
        $normalizedStat = min(1, max(
            0,
            ($statTotal - self::STAT_BONUS_MIN_TOTAL)
                / (self::STAT_BONUS_MAX_TOTAL - self::STAT_BONUS_MIN_TOTAL)
        ));
        $statMultiplier = 1
            + (self::STAT_BONUS_MAX_MULTIPLIER - 1) * $normalizedStat;

        return $dexWeight * $statMultiplier;
    }

    public static function initPool(\MeekroDB $db)
    {
        $jsonData = Json::decode(file_get_contents(__DIR__ . '/Pool/UnderS100.json'));
        $columns = $jsonData['columns'];
        $sqlValues = [];
        foreach ($jsonData['data'] as $idx => $rawItem) {
            if (count($rawItem) !== count($columns)) {
                throw new \RuntimeException(($rawItem[0] ?? (string) $idx) . ' Error');
            }
            $item = array_combine($columns, $rawItem);
            $uniqueName = sprintf('A100%04d', $idx + 1);
            $item['uniqueName'] = $uniqueName;
            $item['event100Growth'] = true;
            $sqlValues[] = [
                'unique_name' => $uniqueName,
                'info' => Json::encode($item),
            ];
        }
        $db->insert('select_pool', $sqlValues);
    }
}
