#!/usr/bin/env php
<?php

declare(strict_types=1);

use sammo\DB;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$_SERVER['REMOTE_ADDR'] ??= '127.0.0.1';
$_SERVER['REQUEST_URI'] ??= '/cli/migrate-general-picture';

require dirname(__DIR__) . '/hwe/lib.php';
require dirname(__DIR__) . '/hwe/func.php';

/** @return never */
function pictureMigrationUsage(int $exitCode = 0): void
{
    $stream = $exitCode === 0 ? STDOUT : STDERR;
    fwrite($stream, <<<'TEXT'
Usage:
  php scripts/migrate-general-picture.php --status
  php scripts/migrate-general-picture.php --apply --backup=/absolute/path/to/pre-migration.sql

--status is read-only. --apply widens general.picture from VARCHAR(40) to
VARCHAR(64), and requires a pre-existing, non-empty SQL backup. Stop web and
daemon traffic before applying; MariaDB/Aria DDL is not transactional.

TEXT);
    exit($exitCode);
}

function pictureColumnCapacity(\MeekroDB $db): ?int
{
    $column = $db->queryFirstRow('SHOW COLUMNS FROM general WHERE Field = %s', 'picture');
    if (!$column || !is_string($column['Type'] ?? null)) {
        return null;
    }
    if (preg_match('/^varchar\((\d+)\)$/i', $column['Type'], $matches) !== 1) {
        return null;
    }
    return (int)$matches[1];
}

function pictureMigrationState(\MeekroDB $db): string
{
    $capacity = pictureColumnCapacity($db);
    if ($capacity === 40) {
        return 'legacy';
    }
    if ($capacity !== null && $capacity >= 64) {
        return 'ready';
    }
    return 'unsupported';
}

function printPictureMigrationStatus(\MeekroDB $db): string
{
    $state = pictureMigrationState($db);
    $capacity = pictureColumnCapacity($db);
    printf("schema_state=%s\npicture_capacity=%s\n", $state, $capacity ?? 'unknown');
    return $state;
}

$options = getopt('', ['help', 'status', 'apply', 'backup:']);
if (isset($options['help'])) {
    pictureMigrationUsage();
}
if (isset($options['status']) === isset($options['apply'])) {
    pictureMigrationUsage(2);
}

$db = DB::db();
if (isset($options['status'])) {
    exit(printPictureMigrationStatus($db) === 'unsupported' ? 2 : 0);
}

$state = pictureMigrationState($db);
if ($state === 'ready') {
    fwrite(STDOUT, "general.picture is already VARCHAR(64) or wider; nothing to do.\n");
    exit(0);
}
if ($state !== 'legacy') {
    fwrite(STDERR, "general.picture is not the supported VARCHAR(40) schema; inspect --status first.\n");
    exit(2);
}

$backup = $options['backup'] ?? null;
if (!is_string($backup) || $backup === '' || $backup[0] !== '/' || !is_file($backup) || filesize($backup) === 0) {
    fwrite(STDERR, "--backup must name a pre-existing, non-empty absolute SQL backup made immediately before migration.\n");
    exit(2);
}
if (!\sammo\tryLock()) {
    fwrite(STDERR, "Unable to acquire the GAME lock.\n");
    exit(3);
}

try {
    $db->query('ALTER TABLE general MODIFY picture VARCHAR(64) NOT NULL');
} finally {
    \sammo\unlock();
}

if (printPictureMigrationStatus($db) !== 'ready') {
    fwrite(STDERR, "general.picture migration verification failed; restore the supplied backup.\n");
    exit(4);
}
fwrite(STDOUT, "general.picture migration completed.\n");
