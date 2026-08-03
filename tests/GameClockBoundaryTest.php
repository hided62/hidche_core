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
            '/\btime\s*\(/i',
        ] as $pattern) {
            self::assertDoesNotMatchRegularExpression($pattern, $source, $relativePath);
        }
    }

    public static function gameSchedulingFiles(): array
    {
        $paths = [
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
            'hwe/sammo/Message.php',
            'hwe/sammo/DiplomaticMessage.php',
            'hwe/sammo/ScoutMessage.php',
            'hwe/sammo/RaiseInvaderMessage.php',
            'hwe/sammo/GeneralAI.php',
            'hwe/sammo/API/Vote/NewVote.php',
            'hwe/sammo/API/Vote/Vote.php',
            'hwe/sammo/API/Vote/GetVoteList.php',
            'hwe/sammo/API/Vote/GetVoteDetail.php',
            'hwe/sammo/API/Vote/AddComment.php',
            'hwe/sammo/API/Nation/SetNotice.php',
            'hwe/j_get_select_npc_token.php',
            'hwe/j_get_select_pool.php',
            'hwe/j_set_npc_control.php',
            'hwe/j_board_article_add.php',
            'hwe/j_board_comment_add.php',
            'hwe/a_traffic.php',
            'hwe/j_server_basic_info.php',
            'hwe/j_diplomacy_send_letter.php',
            'hwe/j_diplomacy_respond_letter.php',
            'hwe/j_diplomacy_destroy_letter.php',
            'hwe/j_diplomacy_rollback_letter.php',
        ];
        foreach (['hwe/sammo/Command', 'hwe/sammo/Event'] as $relativeDirectory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
                __DIR__ . '/../' . $relativeDirectory,
                \FilesystemIterator::SKIP_DOTS,
            ));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $paths[] = $relativeDirectory . '/' . $iterator->getSubPathName();
                }
            }
        }
        $paths = array_values(array_unique($paths));
        sort($paths);
        return array_map(static fn (string $path): array => [$path], $paths);
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
        self::assertDoesNotMatchRegularExpression('/\b(?:CURRENT_TIMESTAMP|NOW\s*\()/i', $schema);
    }

    public function testMonthlyTrafficTimestampUsesLogicalClock(): void
    {
        $source = file_get_contents(__DIR__ . '/../hwe/func.php');
        self::assertIsString($source);
        self::assertMatchesRegularExpression(
            '/function updateTraffic\(\).*?GameClock::fromStorage\(\$gameStor\)->formatNow\(\).*?function CheckOverhead\(/s',
            $source,
        );
        self::assertDoesNotMatchRegularExpression(
            '/function updateTraffic\(\).*?TimeUtil::now\(.*?function CheckOverhead\(/s',
            $source,
        );
    }

    public function testGameLoginDeathCheckUsesTicksWhileSessionTtlRemainsOperational(): void
    {
        $source = file_get_contents(__DIR__ . '/../src/sammo/Session.php');
        self::assertIsString($source);
        self::assertMatchesRegularExpression(
            '/function loginGame\(.*?GameClock::fromStorage\(\$gameStor\)->nowTick\(\).*?GameClock::TICKS_PER_TURN.*?function logoutGame\(/s',
            $source,
        );
        self::assertDoesNotMatchRegularExpression(
            '/function loginGame\(.*?new\s+\\?DateTime(?:Immutable)?\([^)]*turntime.*?function logoutGame\(/s',
            $source,
        );
    }

    public function testBrowserDoesNotCompareProjectedGameDatesToItsWallClock(): void
    {
        $expectations = [
            'hwe/ts/PageVote.vue' => ['currentVote.value.isOpen'],
            'hwe/ts/components/MessagePlate.vue' => ['msg.clockMode === "manual"'],
            'hwe/ts/gateway/entrance.ts' => ['game.isOpen'],
            'hwe/ts/select_npc.ts' => ['logicalClockRunning'],
            'hwe/ts/select_general_from_pool.ts' => ['logicalClockRunning'],
        ];
        foreach ($expectations as $path => $needles) {
            $source = file_get_contents(__DIR__ . '/../' . $path);
            self::assertIsString($source);
            foreach ($needles as $needle) {
                self::assertStringContainsString($needle, $source, $path);
            }
        }
        self::assertStringNotContainsString('formatTime(new Date())', file_get_contents(__DIR__ . '/../hwe/ts/PageVote.vue'));
        self::assertStringNotContainsString('game.opentime <= now', file_get_contents(__DIR__ . '/../hwe/ts/gateway/entrance.ts'));
    }

}
