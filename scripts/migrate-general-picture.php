#!/usr/bin/env php
<?php

declare(strict_types=1);

use sammo\DB;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$options = getopt('', ['help', 'server:', 'status', 'apply', 'backup:', 'server-closed']);
if (isset($options['help'])) {
    pictureMigrationUsage();
}
if (isset($options['status']) === isset($options['apply'])) {
    pictureMigrationUsage(2);
}

$server = $options['server'] ?? null;
if (!is_string($server) || preg_match('/^[a-z][a-z0-9_-]*$/', $server) !== 1) {
    fwrite(STDERR, "--server must name one game-server directory, for example che, kwe, or hwe.\n");
    exit(2);
}

$projectRoot = dirname(__DIR__);
$serverDirectory = $projectRoot . '/' . $server;
foreach (['lib.php', 'func.php', 'd_setting/DB.php'] as $requiredFile) {
    if (!is_file($serverDirectory . '/' . $requiredFile)) {
        fwrite(STDERR, "Server directory '$server' is not a configured game server: missing $requiredFile.\n");
        exit(2);
    }
}

$_SERVER['REMOTE_ADDR'] ??= '127.0.0.1';
$_SERVER['REQUEST_URI'] ??= "/cli/migrate-general-picture/$server";

require $serverDirectory . '/lib.php';
require $serverDirectory . '/func.php';

/** @return never */
function pictureMigrationUsage(int $exitCode = 0): void
{
    $stream = $exitCode === 0 ? STDOUT : STDERR;
    fwrite($stream, <<<'TEXT'
Usage:
  php scripts/migrate-general-picture.php --server=PREFIX --status
  php scripts/migrate-general-picture.php --server=PREFIX --apply --server-closed --backup=/absolute/path/to/pre-migration.sql

PREFIX is one configured game directory such as che, kwe, or hwe. Run status,
backup, apply, and verification separately for every game database; this script
never loops over all servers implicitly.

--status is read-only. --apply widens general.picture and the eight emperior
chief picture columns to VARCHAR(64), adds nullable l12imgsvr through l5imgsvr,
and backfills only uniquely matched historical values from ng_old_generals.
It requires a pre-existing, non-empty SQL backup whenever a schema or data
change is needed. Stop web and daemon traffic before applying; MariaDB/Aria DDL
is not transactional. --apply requires --server-closed as an explicit operator
confirmation that web and daemon traffic has already been stopped. The script
does not acquire or alter the GAME lock; it only takes a separate MariaDB named
lock to prevent two picture migrations from running together. Unmatched or
ambiguous historical values remain NULL.

TEXT);
    exit($exitCode);
}

/** @return list<int> */
function emperiorPictureLevels(): array
{
    return [12, 11, 10, 9, 8, 7, 6, 5];
}

/** @return list<array{string, string, int}> */
function pictureMigrationColumns(): array
{
    $columns = [['general', 'picture', 40]];
    foreach (emperiorPictureLevels() as $level) {
        $columns[] = ['emperior', "l{$level}pic", 32];
    }
    return $columns;
}

/** @return array<string, mixed>|null */
function migrationColumnInfo(\MeekroDB $db, string $table, string $field): ?array
{
    $column = $db->queryFirstRow("SHOW COLUMNS FROM `$table` WHERE Field = %s", $field);
    return is_array($column) ? $column : null;
}

function pictureColumnCapacity(\MeekroDB $db, string $table, string $field): ?int
{
    $column = migrationColumnInfo($db, $table, $field);
    if (!$column || !is_string($column['Type'] ?? null)) {
        return null;
    }
    if (preg_match('/^varchar\((\d+)\)$/i', $column['Type'], $matches) !== 1) {
        return null;
    }
    return (int)$matches[1];
}

function imgsvrColumnIsCompatible(\MeekroDB $db, int $level): bool
{
    $column = migrationColumnInfo($db, 'emperior', "l{$level}imgsvr");
    if (!$column || !is_string($column['Type'] ?? null)) {
        return false;
    }
    return preg_match('/^(?:tinyint|smallint|mediumint|int|bigint)\(\d+\)(?: unsigned)?$/i', $column['Type']) === 1
        && strtoupper((string)($column['Null'] ?? '')) === 'YES';
}

function pictureMigrationState(\MeekroDB $db): string
{
    $needsMigration = false;
    foreach (pictureMigrationColumns() as [$table, $field, $legacyCapacity]) {
        $capacity = pictureColumnCapacity($db, $table, $field);
        if ($capacity === $legacyCapacity) {
            $needsMigration = true;
            continue;
        }
        if ($capacity === null || $capacity < 64) {
            return 'unsupported';
        }
    }

    foreach (emperiorPictureLevels() as $level) {
        if (migrationColumnInfo($db, 'emperior', "l{$level}imgsvr") === null) {
            $needsMigration = true;
            continue;
        }
        if (!imgsvrColumnIsCompatible($db, $level)) {
            return 'unsupported';
        }
    }

    return $needsMigration ? 'legacy' : 'ready';
}

function imgsvrColumnsExist(\MeekroDB $db): bool
{
    foreach (emperiorPictureLevels() as $level) {
        if (migrationColumnInfo($db, 'emperior', "l{$level}imgsvr") === null) {
            return false;
        }
    }
    return true;
}

function unresolvedEmperiorImgsvrCount(\MeekroDB $db): ?int
{
    if (!imgsvrColumnsExist($db)) {
        return null;
    }
    $terms = array_map(
        static fn(int $level): string => "(`l{$level}imgsvr` IS NULL)",
        emperiorPictureLevels(),
    );
    $count = $db->queryFirstField('SELECT SUM(' . implode(' + ', $terms) . ') FROM emperior');
    return $count === null ? 0 : (int)$count;
}

/** @return list<array{no: int|string, imgsvr: int|string}> */
function recoverableEmperiorImgsvrRows(\MeekroDB $db, int $level): array
{
    $nameField = "l{$level}name";
    $pictureField = "l{$level}pic";
    $imgsvrField = "l{$level}imgsvr";
    return $db->query(
        "SELECT e.`no`, MIN(CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(og.`data`, '$.imgsvr')), '-1') AS SIGNED)) AS `imgsvr`
         FROM `emperior` e
         JOIN `ng_old_generals` og
           ON og.`server_id` = e.`server_id`
          AND og.`name` = e.`$nameField`
          AND SUBSTRING_INDEX(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(og.`data`, '$.picture')), ''), '?=', 1)
              = SUBSTRING_INDEX(COALESCE(e.`$pictureField`, ''), '?=', 1)
          AND CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(og.`data`, '$.officer_level')), '-1') AS SIGNED) = %i
         WHERE e.`$imgsvrField` IS NULL
         GROUP BY e.`no`
         HAVING COUNT(*) = 1 AND `imgsvr` IN (0, 1)",
        $level,
    );
}

function recoverableEmperiorImgsvrCount(\MeekroDB $db): int
{
    if (!imgsvrColumnsExist($db)) {
        return 0;
    }
    $count = 0;
    foreach (emperiorPictureLevels() as $level) {
        $count += count(recoverableEmperiorImgsvrRows($db, $level));
    }
    return $count;
}

function backfillEmperiorImgsvr(\MeekroDB $db): int
{
    $updated = 0;
    foreach (emperiorPictureLevels() as $level) {
        $field = "l{$level}imgsvr";
        foreach (recoverableEmperiorImgsvrRows($db, $level) as $row) {
            $db->update(
                'emperior',
                [$field => (int)$row['imgsvr']],
                "`no`=%i AND `$field` IS NULL",
                (int)$row['no'],
            );
            $updated++;
        }
    }
    return $updated;
}

function printPictureMigrationStatus(\MeekroDB $db, string $server): string
{
    $state = pictureMigrationState($db);
    printf("server=%s\n", $server);
    printf("schema_state=%s\n", $state);
    foreach (pictureMigrationColumns() as [$table, $field]) {
        $capacity = pictureColumnCapacity($db, $table, $field);
        $statusKey = $table === 'general' && $field === 'picture'
            ? 'picture_capacity'
            : "{$table}_{$field}_capacity";
        printf("%s=%s\n", $statusKey, $capacity ?? 'unknown');
    }
    foreach (emperiorPictureLevels() as $level) {
        $field = "l{$level}imgsvr";
        $column = migrationColumnInfo($db, 'emperior', $field);
        printf(
            "emperior_%s=%s\n",
            $field,
            $column === null ? 'missing' : strtolower((string)$column['Type']),
        );
    }
    $unresolved = unresolvedEmperiorImgsvrCount($db);
    printf("unresolved_emperior_imgsvr=%s\n", $unresolved ?? 'unknown');
    return $state;
}

function requirePictureMigrationBackup(mixed $backup): string
{
    if (!is_string($backup) || $backup === '' || $backup[0] !== '/' || !is_file($backup) || filesize($backup) === 0) {
        fwrite(STDERR, "--backup must name a pre-existing, non-empty absolute SQL backup made immediately before migration.\n");
        exit(2);
    }
    return $backup;
}

function acquirePictureMigrationLock(\MeekroDB $db, string $server): bool
{
    return (int)$db->queryFirstField(
        'SELECT GET_LOCK(%s, 0)',
        "sammo-picture-migration-$server",
    ) === 1;
}

function releasePictureMigrationLock(\MeekroDB $db, string $server): void
{
    $db->queryFirstField(
        'SELECT RELEASE_LOCK(%s)',
        "sammo-picture-migration-$server",
    );
}

$db = DB::db();
if (isset($options['status'])) {
    exit(printPictureMigrationStatus($db, $server) === 'unsupported' ? 2 : 0);
}

$state = pictureMigrationState($db);
if ($state === 'unsupported') {
    fwrite(STDERR, "One or more picture columns have an unsupported schema; inspect --status first.\n");
    exit(2);
}

$recoverableBefore = $state === 'ready' ? recoverableEmperiorImgsvrCount($db) : 0;
if ($state === 'ready' && $recoverableBefore === 0) {
    fwrite(STDOUT, "Picture schema for $server is ready and no deterministic IMGSVR backfill candidates remain; nothing to do.\n");
    printPictureMigrationStatus($db, $server);
    exit(0);
}

requirePictureMigrationBackup($options['backup'] ?? null);
$serverClosed = isset($options['server-closed']);
if (!$serverClosed) {
    fwrite(
        STDERR,
        "WARNING: Picture migration must run while $server web and daemon traffic is stopped. After stopping them, rerun with --server-closed.\n",
    );
    exit(3);
}
fwrite(
    STDERR,
    "WARNING: --server-closed is an operator confirmation; this script cannot verify that $server web and daemon traffic is stopped.\n",
);
if (!acquirePictureMigrationLock($db, $server)) {
    fwrite(STDERR, "Another picture migration is already running for $server.\n");
    exit(3);
}

$backfilled = 0;
try {
    if (pictureColumnCapacity($db, 'general', 'picture') === 40) {
        $db->query('ALTER TABLE general MODIFY picture VARCHAR(64) NOT NULL');
    }

    $emperiorClauses = [];
    foreach (emperiorPictureLevels() as $level) {
        $pictureField = "l{$level}pic";
        $imgsvrField = "l{$level}imgsvr";
        if (pictureColumnCapacity($db, 'emperior', $pictureField) === 32) {
            $emperiorClauses[] = "MODIFY `$pictureField` VARCHAR(64) NULL DEFAULT ''";
        }
        if (migrationColumnInfo($db, 'emperior', $imgsvrField) === null) {
            $emperiorClauses[] = "ADD COLUMN `$imgsvrField` INT(1) NULL DEFAULT NULL AFTER `$pictureField`";
        }
    }
    if ($emperiorClauses !== []) {
        $db->query('ALTER TABLE emperior ' . implode(', ', $emperiorClauses));
    }

    $backfilled = backfillEmperiorImgsvr($db);
} finally {
    releasePictureMigrationLock($db, $server);
}

if (printPictureMigrationStatus($db, $server) !== 'ready') {
    fwrite(STDERR, "Picture-column migration verification failed; restore the supplied backup.\n");
    exit(4);
}
printf("Picture-column migration for %s completed; backfilled_imgsvr=%d.\n", $server, $backfilled);
