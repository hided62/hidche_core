<?php

namespace sammo\GeneralPool;

use sammo\AbsFromUserPool;
use sammo\CentennialAllStarGrowthService;
use sammo\Json;
use sammo\RandUtil;

class SPoolUnderU100 extends AbsFromUserPool
{
    public function __construct(\MeekroDB $db, RandUtil $rng, array $info, string $validUntil)
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

    public static function initPool(\MeekroDB $db)
    {
        // 현재 ref 저장소에 보존된 공식 클래식 명장 자료는 1~29기 자료다.
        // 풀 형식과 event100Unique는 이후 30~99기 자료를 같은 형태로 합칠 수 있다.
        $jsonData = Json::decode(file_get_contents(__DIR__ . '/Pool/UnderS30.json'));
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
