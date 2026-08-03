<?php

namespace sammo;

use PHPUnit\Framework\TestCase;

final class GameClockBoundaryTest extends TestCase
{
    /** @dataProvider gameSchedulingFiles */
    public function testGameSchedulingCodeDoesNotReadWallOrDatabaseClock(string $relativePath): void
    {
        $source = file_get_contents(__DIR__ . '/../' . $relativePath);
        self::assertIsString($source);

        foreach ([
            '/TimeUtil::now(?:DateTimeImmutable)?\s*\(/',
            '/new\s+\\\\?DateTime(?:Immutable)?\s*\(\s*\)/',
            '/\bNOW\s*\(/i',
            '/\bCURRENT_TIMESTAMP\b/i',
            '/\bCURDATE\s*\(/i',
        ] as $pattern) {
            self::assertDoesNotMatchRegularExpression($pattern, $source, $relativePath);
        }
    }

    public static function gameSchedulingFiles(): array
    {
        return array_map(static fn (string $path): array => [$path], [
            'hwe/sammo/TurnExecutionHelper.php',
            'hwe/sammo/Auction.php',
            'hwe/sammo/AuctionBasicResource.php',
            'hwe/sammo/AuctionUniqueItem.php',
            'hwe/func_auction.php',
            'hwe/func_tournament.php',
            'hwe/c_tournament.php',
            'hwe/sammo/AbsFromUserPool.php',
            'hwe/sammo/GeneralPool/RandomNameGeneral.php',
            'hwe/sammo/API/General/DieOnPrestart.php',
        ]);
    }

    public function testTickSchemaDoesNotUseDatabaseDefaultsForGameSchedules(): void
    {
        $schema = file_get_contents(__DIR__ . '/../hwe/sql/schema.sql');
        self::assertIsString($schema);
        foreach ([
            '`turntime` BIGINT',
            '`recent_war` BIGINT',
            '`last_refresh` BIGINT',
            '`time` BIGINT',
            '`valid_until` BIGINT',
            '`reserved_until` BIGINT',
            '`open_tick` BIGINT',
            '`close_tick` BIGINT',
        ] as $expected) {
            self::assertStringContainsString($expected, $schema);
        }
    }
}
