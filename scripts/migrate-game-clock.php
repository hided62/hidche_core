#!/usr/bin/env php
<?php

declare(strict_types=1);

use sammo\DB;
use sammo\GameClock;
use sammo\Json;
use sammo\KVStorage;
use sammo\Util;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$_SERVER['REMOTE_ADDR'] ??= '127.0.0.1';
$_SERVER['REQUEST_URI'] ??= '/cli/migrate-game-clock';

require dirname(__DIR__) . '/hwe/lib.php';
require dirname(__DIR__) . '/hwe/func.php';

/** @return never */
function migrationUsage(int $exitCode = 0): void
{
    $stream = $exitCode === 0 ? STDOUT : STDERR;
    fwrite($stream, <<<'TEXT'
Usage:
  php scripts/migrate-game-clock.php --status
  php scripts/migrate-game-clock.php --apply --backup=/absolute/path/to/pre-migration.sql

--status is read-only. --apply requires a pre-existing, non-empty SQL backup and
the GAME lock. Original DATETIME columns and game_env values are retained with
the *_wall_backup suffix so recovery does not depend only on reverse arithmetic.
Stop web/daemon traffic before applying; Aria DDL is not transactional.
TEXT);
    exit($exitCode);
}

/** @return array<string,string> */
function tableColumnTypes(\MeekroDB $db, string $table): array
{
    $result = [];
    foreach ($db->query("SHOW COLUMNS FROM %b", $table) as $column) {
        $result[(string)$column['Field']] = strtolower((string)$column['Type']);
    }
    return $result;
}

function isDateColumn(?string $type): bool
{
    return $type !== null && str_starts_with($type, 'datetime');
}

function isBigIntColumn(?string $type): bool
{
    return $type !== null && str_starts_with($type, 'bigint');
}

/** @return array{state:string,details:array<string,array<string,string>>} */
function inspectMigration(\MeekroDB $db): array
{
    $details = [];
    foreach (['general', 'general_access_log', 'message', 'select_npc_token', 'select_pool', 'ng_auction'] as $table) {
        $details[$table] = tableColumnTypes($db, $table);
    }

    $old = isDateColumn($details['general']['turntime'] ?? null)
        && isDateColumn($details['general']['recent_war'] ?? null)
        && isDateColumn($details['general_access_log']['last_refresh'] ?? null)
        && isDateColumn($details['message']['time'] ?? null)
        && isDateColumn($details['message']['valid_until'] ?? null)
        && isDateColumn($details['select_npc_token']['valid_until'] ?? null)
        && isDateColumn($details['select_npc_token']['pick_more_from'] ?? null)
        && isDateColumn($details['select_pool']['reserved_until'] ?? null)
        && isDateColumn($details['ng_auction']['open_date'] ?? null)
        && isDateColumn($details['ng_auction']['close_date'] ?? null);
    $new = isBigIntColumn($details['general']['turntime'] ?? null)
        && isBigIntColumn($details['general']['recent_war'] ?? null)
        && isBigIntColumn($details['general_access_log']['last_refresh'] ?? null)
        && isBigIntColumn($details['message']['time'] ?? null)
        && isBigIntColumn($details['message']['valid_until'] ?? null)
        && isBigIntColumn($details['select_npc_token']['valid_until'] ?? null)
        && isBigIntColumn($details['select_npc_token']['pick_more_from'] ?? null)
        && isBigIntColumn($details['select_pool']['reserved_until'] ?? null)
        && isBigIntColumn($details['ng_auction']['open_tick'] ?? null)
        && isBigIntColumn($details['ng_auction']['close_tick'] ?? null);

    return [
        'state' => $old ? 'legacy' : ($new ? 'tick' : 'partial-or-unknown'),
        'details' => $details,
    ];
}

function printMigrationStatus(\MeekroDB $db): string
{
    $inspection = inspectMigration($db);
    printf("schema_state=%s\n", $inspection['state']);
    foreach ($inspection['details'] as $table => $columns) {
        $interesting = array_filter(
            $columns,
            static fn (string $name): bool => preg_match(
                '/^(turntime|recent_war|last_refresh|time|valid_until|pick_more_from|reserved_until|open_date|close_date|open_tick|close_tick|.*_wall_backup)$/',
                $name,
            ) === 1,
            ARRAY_FILTER_USE_KEY,
        );
        printf("%s %s\n", $table, Json::encode($interesting));
    }
    return $inspection['state'];
}

/** Convert a DATETIME column into a staged BIGINT tick column. */
function fillTickColumn(
    \MeekroDB $db,
    string $table,
    string $dateColumn,
    string $tickColumn,
    string $baseTime,
    int $ticksPerSecond,
    bool $nullable,
): void {
    $nullSql = $nullable ? ' NULL DEFAULT NULL' : ' NOT NULL';
    $db->query("ALTER TABLE %b ADD COLUMN %b BIGINT{$nullSql}", $table, $tickColumn);
    $db->query(
        "UPDATE %b SET %b = (TIMESTAMPDIFF(MICROSECOND, %s, %b) * %i) DIV 1000000",
        $table,
        $tickColumn,
        $baseTime,
        $dateColumn,
        $ticksPerSecond,
    );
    $missing = Util::toInt($db->queryFirstField(
        "SELECT COUNT(*) FROM %b WHERE %b IS NOT NULL AND %b IS NULL",
        $table,
        $dateColumn,
        $tickColumn,
    ));
    if ($missing !== 0) {
        throw new RuntimeException("{$table}.{$tickColumn} 변환 누락: {$missing}");
    }
}

function assertSafeTickColumn(\MeekroDB $db, string $table, string $column): void
{
    $unsafe = Util::toInt($db->queryFirstField(
        'SELECT COUNT(*) FROM %b WHERE %b > %i OR %b < %i',
        $table,
        $column,
        GameClock::MAX_SAFE_TICK,
        $column,
        -GameClock::MAX_SAFE_TICK,
    ));
    if ($unsafe !== 0) {
        throw new RuntimeException("{$table}.{$column} JavaScript safe integer 초과: {$unsafe}");
    }
}

$options = getopt('', ['help', 'status', 'apply', 'backup:']);
if (isset($options['help'])) {
    migrationUsage();
}
if (isset($options['status']) === isset($options['apply'])) {
    migrationUsage(2);
}

$db = DB::db();
if (isset($options['status'])) {
    exit(printMigrationStatus($db) === 'partial-or-unknown' ? 2 : 0);
}

$backup = $options['backup'] ?? null;
if (!is_string($backup) || $backup === '' || $backup[0] !== '/' || !is_file($backup) || filesize($backup) === 0) {
    fwrite(STDERR, "--backup에는 적용 직전에 만든 비어 있지 않은 절대경로 SQL 백업을 지정해야 합니다.\n");
    exit(2);
}
if (inspectMigration($db)['state'] !== 'legacy') {
    fwrite(STDERR, "legacy 스키마가 아니므로 적용하지 않습니다. --status 결과를 확인해 주세요.\n");
    exit(2);
}
if (!\sammo\tryLock()) {
    fwrite(STDERR, "GAME lock을 획득하지 못했습니다.\n");
    exit(3);
}

try {
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $legacy = $gameStor->getValues(['starttime', 'turntime', 'opentime', 'tnmt_time', 'turnterm']);
    $turnTerm = Util::toInt($legacy['turnterm']);
    $baseTime = new DateTimeImmutable((string)$legacy['starttime']);
    $wallNow = GameClock::readWallTime();
    $conversionClock = new GameClock(
        $baseTime,
        $turnTerm,
        0,
        GameClock::MODE_REALTIME,
        $wallNow,
        static fn (): DateTimeImmutable => $wallNow,
    );
    $baseTimeString = $baseTime->format('Y-m-d H:i:s.u');
    $ticksPerSecond = $conversionClock->ticksPerSecond();

    fillTickColumn($db, 'general', 'turntime', 'turntime_game_tick', $baseTimeString, $ticksPerSecond, false);
    fillTickColumn($db, 'general', 'recent_war', 'recent_war_game_tick', $baseTimeString, $ticksPerSecond, true);
    fillTickColumn($db, 'general_access_log', 'last_refresh', 'last_refresh_game_tick', $baseTimeString, $ticksPerSecond, true);
    fillTickColumn($db, 'message', 'time', 'time_game_tick', $baseTimeString, $ticksPerSecond, false);
    fillTickColumn($db, 'message', 'valid_until', 'valid_until_game_tick', $baseTimeString, $ticksPerSecond, false);
    $db->query(
        'UPDATE message SET valid_until_game_tick = %i WHERE YEAR(valid_until) >= 9000',
        GameClock::MAX_SAFE_TICK,
    );
    fillTickColumn($db, 'select_npc_token', 'valid_until', 'valid_until_game_tick', $baseTimeString, $ticksPerSecond, false);
    fillTickColumn($db, 'select_npc_token', 'pick_more_from', 'pick_more_from_game_tick', $baseTimeString, $ticksPerSecond, false);
    fillTickColumn($db, 'select_pool', 'reserved_until', 'reserved_until_game_tick', $baseTimeString, $ticksPerSecond, true);
    fillTickColumn($db, 'ng_auction', 'open_date', 'open_game_tick', $baseTimeString, $ticksPerSecond, false);
    fillTickColumn($db, 'ng_auction', 'close_date', 'close_game_tick', $baseTimeString, $ticksPerSecond, false);
    foreach ([
        ['general', 'turntime_game_tick'],
        ['general', 'recent_war_game_tick'],
        ['general_access_log', 'last_refresh_game_tick'],
        ['message', 'time_game_tick'],
        ['message', 'valid_until_game_tick'],
        ['select_npc_token', 'valid_until_game_tick'],
        ['select_npc_token', 'pick_more_from_game_tick'],
        ['select_pool', 'reserved_until_game_tick'],
        ['ng_auction', 'open_game_tick'],
        ['ng_auction', 'close_game_tick'],
    ] as [$table, $column]) {
        assertSafeTickColumn($db, $table, $column);
    }

    foreach ($db->query('SELECT no, aux FROM general') as $row) {
        $aux = Json::decode((string)$row['aux']);
        $nextChange = $aux['next_change'] ?? null;
        if (is_string($nextChange) && !ctype_digit(ltrim($nextChange, '-'))) {
            $aux['next_change_wall_backup'] = $nextChange;
            $aux['next_change'] = $conversionClock->dateTimeToTick(new DateTimeImmutable($nextChange));
            $db->update('general', ['aux' => Json::encode($aux)], 'no = %i', $row['no']);
        }
    }
    foreach ($db->query('SELECT id, detail FROM ng_auction') as $row) {
        $detail = Json::decode((string)$row['detail']);
        $legacyLimit = $detail['availableLatestBidCloseDate'] ?? null;
        if (is_string($legacyLimit) && $legacyLimit !== '') {
            $detail['availableLatestBidCloseDateWallBackup'] = $legacyLimit;
            $detail['availableLatestBidCloseTick'] = $conversionClock->dateTimeToTick(new DateTimeImmutable($legacyLimit));
        } else {
            $detail['availableLatestBidCloseTick'] = null;
        }
        unset($detail['availableLatestBidCloseDate']);
        $db->update('ng_auction', ['detail' => Json::encode($detail)], 'id = %i', $row['id']);
    }
    foreach ($db->query('SELECT namespace, value FROM nation_env WHERE `key` = %s', 'last천도Trial') as $row) {
        $trial = Json::decode((string)$row['value']);
        if (!is_array($trial) || !isset($trial[1]) || !is_string($trial[1]) || ctype_digit(ltrim($trial[1], '-'))) {
            continue;
        }
        $db->insertUpdate('nation_env', [
            'namespace' => $row['namespace'],
            'key' => 'last천도Trial_wall_backup',
            'value' => Json::encode($trial),
        ]);
        $trial[1] = $conversionClock->dateTimeToTick(new DateTimeImmutable($trial[1]));
        $db->update(
            'nation_env',
            ['value' => Json::encode($trial)],
            'namespace = %i AND `key` = %s',
            $row['namespace'],
            'last천도Trial',
        );
    }

    $db->query(
        'ALTER TABLE general '
        . 'DROP INDEX turntime, DROP INDEX troop, '
        . 'CHANGE turntime turntime_wall_backup DATETIME(6) NULL DEFAULT NULL, '
        . 'CHANGE recent_war recent_war_wall_backup DATETIME(6) NULL DEFAULT NULL, '
        . 'CHANGE turntime_game_tick turntime BIGINT NOT NULL, '
        . 'CHANGE recent_war_game_tick recent_war BIGINT NULL DEFAULT NULL, '
        . 'ADD INDEX turntime (turntime, no), ADD INDEX troop (troop, turntime)'
    );
    $db->query(
        'ALTER TABLE general_access_log '
        . 'CHANGE last_refresh last_refresh_wall_backup DATETIME NULL DEFAULT NULL, '
        . 'CHANGE last_refresh_game_tick last_refresh BIGINT NULL DEFAULT NULL'
    );
    $db->query(
        'ALTER TABLE message '
        . 'CHANGE time time_wall_backup DATETIME NULL DEFAULT NULL, '
        . 'CHANGE valid_until valid_until_wall_backup DATETIME NULL DEFAULT NULL, '
        . 'CHANGE time_game_tick time BIGINT NOT NULL, '
        . 'CHANGE valid_until_game_tick valid_until BIGINT NOT NULL'
    );
    $db->query(
        'ALTER TABLE select_npc_token DROP INDEX valid_until, '
        . 'CHANGE valid_until valid_until_wall_backup DATETIME NULL DEFAULT NULL, '
        . 'CHANGE pick_more_from pick_more_from_wall_backup DATETIME NULL DEFAULT NULL, '
        . 'CHANGE valid_until_game_tick valid_until BIGINT NOT NULL, '
        . 'CHANGE pick_more_from_game_tick pick_more_from BIGINT NOT NULL, '
        . 'ADD INDEX valid_until (valid_until)'
    );
    $db->query(
        'ALTER TABLE select_pool DROP INDEX reserved_until, '
        . 'CHANGE reserved_until reserved_until_wall_backup DATETIME NULL DEFAULT NULL, '
        . 'CHANGE reserved_until_game_tick reserved_until BIGINT NULL DEFAULT NULL, '
        . 'ADD INDEX reserved_until (reserved_until, general_id)'
    );
    $db->query(
        'ALTER TABLE ng_auction DROP INDEX by_close, '
        . 'CHANGE open_date open_date_wall_backup DATETIME NULL DEFAULT NULL, '
        . 'CHANGE close_date close_date_wall_backup DATETIME NULL DEFAULT NULL, '
        . 'CHANGE open_game_tick open_tick BIGINT NOT NULL, '
        . 'CHANGE close_game_tick close_tick BIGINT NOT NULL, '
        . 'ADD INDEX by_close (finished, type, close_tick)'
    );

    foreach (['starttime', 'turntime', 'opentime', 'tnmt_time'] as $key) {
        $value = $legacy[$key] ?? null;
        $gameStor->{"{$key}_wall_backup"} = $value;
        $gameStor->{$key} = $value === null || $value === ''
            ? null
            : $conversionClock->dateTimeToTick(new DateTimeImmutable((string)$value));
    }
    GameClock::initializeStorage(
        $gameStor,
        $baseTime,
        $turnTerm,
        $conversionClock->dateTimeToTick($wallNow),
        GameClock::MODE_REALTIME,
        $wallNow,
    );
} finally {
    \sammo\unlock();
}

if (printMigrationStatus($db) !== 'tick') {
    fwrite(STDERR, "마이그레이션 후 스키마 검증에 실패했습니다. 백업과 *_wall_backup을 이용해 복구해 주세요.\n");
    exit(4);
}
fwrite(STDOUT, "게임 시계 migration 완료. *_wall_backup은 별도 검증 후에만 수동 제거해 주세요.\n");
