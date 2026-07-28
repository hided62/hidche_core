<?php

use PHPUnit\Framework\TestCase;

final class CentennialAllStarPoolTest extends TestCase
{
    private const POOL_PATH = __DIR__ . '/../hwe/sammo/GeneralPool/Pool/UnderS100.json';

    public function testPoolCoversEveryCompletedNonEventPhase(): void
    {
        $pool = json_decode(
            file_get_contents(self::POOL_PATH),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        self::assertSame(
            [
                'generalName',
                'leadership',
                'strength',
                'intel',
                'specialDomestic',
                'dex',
                'imgsvr',
                'picture',
                'sourcePhase',
                'sourceServerId',
                'sourceGeneralNo',
                'selectionReasons',
            ],
            $pool['columns']
        );
        self::assertCount(4682, $pool['data']);
        self::assertSame(
            [
                5, 10, 15, 20, 25, 30, 35, 40, 45, 50,
                55, 60, 65, 70, 75, 80, 85, 90, 95,
            ],
            $pool['excludedEventPhases']
        );

        $column = array_flip($pool['columns']);
        $phases = [];
        $chiefCounts = [];
        $sourceKeys = [];
        $generalNames = [];
        foreach ($pool['data'] as $row) {
            self::assertCount(count($pool['columns']), $row);

            $phase = $row[$column['sourcePhase']];
            $sourceKey = "{$phase}:{$row[$column['sourceGeneralNo']]}";
            self::assertArrayNotHasKey($sourceKey, $sourceKeys);
            $sourceKeys[$sourceKey] = true;
            $phases[$phase] = true;

            $generalName = $row[$column['generalName']];
            self::assertArrayNotHasKey($generalName, $generalNames);
            $generalNames[$generalName] = true;

            self::assertCount(5, $row[$column['dex']]);
            self::assertNotEmpty($row[$column['selectionReasons']]);
            foreach ($row[$column['selectionReasons']] as $reason) {
                self::assertMatchesRegularExpression(
                    '/^(hall:[a-z0-9_]+|chief:(?:5|6|7|8|9|10|11|12))$/',
                    $reason
                );
                if (str_starts_with($reason, 'chief:')) {
                    $chiefCounts[$phase] = ($chiefCounts[$phase] ?? 0) + 1;
                }
            }
        }

        ksort($phases, SORT_NUMERIC);
        $expectedPhases = array_values(array_diff(
            range(1, 99),
            $pool['excludedEventPhases']
        ));
        self::assertSame($expectedPhases, array_keys($phases));
        foreach ($expectedPhases as $phase) {
            self::assertSame(
                8,
                $chiefCounts[$phase] ?? 0,
                "phase {$phase} must include the ruler and seven chiefs"
            );
        }
    }

    public function testEveryHistoricalEventSpecialHasAnImplementation(): void
    {
        $pool = json_decode(
            file_get_contents(self::POOL_PATH),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $column = array_flip($pool['columns']);

        foreach ($pool['data'] as $row) {
            $special = $row[$column['specialDomestic']];
            if ($special === null) {
                continue;
            }
            self::assertFileExists(
                __DIR__ . "/../hwe/sammo/ActionSpecialDomestic/{$special}.php"
            );
        }
    }
}
