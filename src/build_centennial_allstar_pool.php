<?php

declare(strict_types=1);

/**
 * Build the static 100th-season all-star pool from a tab-separated query result.
 *
 * Usage:
 *   mariadb --batch --raw --skip-column-names DATABASE \
 *     < src/centennial_allstar_candidates.sql \
 *     | php src/build_centennial_allstar_pool.php OUTPUT.json
 */

const OUTPUT_COLUMNS = [
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
];

const EXCLUDED_EVENT_PHASES = [
    5, 10, 15, 20, 25, 30, 35, 40, 45, 50,
    55, 60, 65, 70, 75, 80, 85, 90, 95,
];

const LEGACY_SPECIAL_WAR_MAP = [
    40 => 'che_event_귀병',
    41 => 'che_event_신산',
    42 => 'che_event_환술',
    43 => 'che_event_집중',
    44 => 'che_event_신중',
    45 => 'che_event_반계',
    50 => 'che_event_보병',
    51 => 'che_event_궁병',
    52 => 'che_event_기병',
    53 => 'che_event_공성',
    60 => 'che_event_돌격',
    61 => 'che_event_무쌍',
    62 => 'che_event_견고',
    63 => 'che_event_위압',
    70 => 'che_event_저격',
    71 => 'che_event_필살',
    72 => 'che_event_징병',
    73 => 'che_event_의술',
    74 => 'che_event_격노',
    75 => 'che_event_척사',
];

function fail(string $message): never
{
    fwrite(STDERR, "error: {$message}\n");
    exit(1);
}

function firstInt(array $data, array $keys): int
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $data) && is_numeric($data[$key])) {
            return (int) $data[$key];
        }
    }
    fail('missing numeric field: ' . implode(' or ', $keys));
}

function normalizeEventSpecial(mixed $rawSpecial): ?string
{
    if (is_numeric($rawSpecial)) {
        return LEGACY_SPECIAL_WAR_MAP[(int) $rawSpecial] ?? null;
    }
    if (
        !is_string($rawSpecial)
        || $rawSpecial === ''
        || $rawSpecial === '0'
        || strcasecmp($rawSpecial, 'none') === 0
    ) {
        return null;
    }
    if (str_starts_with($rawSpecial, 'che_event_')) {
        return $rawSpecial;
    }
    if (str_starts_with($rawSpecial, 'che_')) {
        return 'che_event_' . substr($rawSpecial, strlen('che_'));
    }
    fail("unknown historical war special: {$rawSpecial}");
}

function decodeSourceRow(string $line, int $lineNo): array
{
    $fields = explode("\t", rtrim($line, "\r\n"), 6);
    if (count($fields) !== 6) {
        fail("line {$lineNo}: expected 6 tab-separated fields, got " . count($fields));
    }

    [$phase, $serverId, $generalNo, $name, $rawData, $rawReasons] = $fields;
    $data = json_decode($rawData, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        fail("line {$lineNo}: general data is not an object");
    }

    $phaseNo = (int) $phase;
    $sourceGeneralNo = (int) $generalNo;
    if ($phaseNo < 1 || $phaseNo > 99 || $sourceGeneralNo <= 2) {
        fail("line {$lineNo}: invalid phase/general number");
    }

    $sourceName = trim($name);
    if ($sourceName === '') {
        fail("line {$lineNo}: empty general name");
    }
    $generalName = sprintf('【%d기】%s', $phaseNo, $sourceName);
    if (mb_strlen($generalName) > 32) {
        fail("line {$lineNo}: generated name exceeds 32 characters: {$generalName}");
    }

    $dex = [
        firstInt($data, ['dex1', 'dex0']),
        firstInt($data, ['dex2', 'dex10']),
        firstInt($data, ['dex3', 'dex20']),
        firstInt($data, ['dex4', 'dex30']),
        firstInt($data, ['dex5', 'dex40']),
    ];
    foreach ($dex as $value) {
        if ($value < 0) {
            fail("line {$lineNo}: negative dex value");
        }
    }

    $reasons = $rawReasons === '' ? [] : explode(',', $rawReasons);
    sort($reasons, SORT_STRING);

    return [
        $generalName,
        firstInt($data, ['leadership', 'leader']),
        firstInt($data, ['strength', 'power']),
        firstInt($data, ['intel']),
        normalizeEventSpecial($data['special2'] ?? null),
        $dex,
        firstInt($data, ['imgsvr']),
        is_string($data['picture'] ?? null) && $data['picture'] !== ''
            ? $data['picture']
            : 'default.jpg',
        $phaseNo,
        $serverId,
        $sourceGeneralNo,
        $reasons,
    ];
}

if ($argc !== 2) {
    fail('usage: php build_centennial_allstar_pool.php OUTPUT.json');
}

$outputPath = $argv[1];
$rows = [];
$seenSources = [];
$nameIndexes = [];
$phaseCounts = [];
$chiefCounts = [];
$reasonCounts = [];
$lineNo = 0;

while (($line = fgets(STDIN)) !== false) {
    $lineNo++;
    if (trim($line) === '') {
        continue;
    }
    try {
        $row = decodeSourceRow($line, $lineNo);
    } catch (JsonException $e) {
        fail("line {$lineNo}: invalid JSON: {$e->getMessage()}");
    }

    $sourceKey = "{$row[8]}:{$row[10]}";
    if (isset($seenSources[$sourceKey])) {
        fail("line {$lineNo}: duplicate source general {$sourceKey}");
    }
    $seenSources[$sourceKey] = true;
    $nameIndexes[$row[0]][] = count($rows);
    $phaseCounts[$row[8]] = ($phaseCounts[$row[8]] ?? 0) + 1;
    foreach ($row[11] as $reason) {
        $reasonGroup = str_starts_with($reason, 'chief:') ? 'chief' : 'hall';
        $reasonCounts[$reasonGroup] = ($reasonCounts[$reasonGroup] ?? 0) + 1;
        if ($reasonGroup === 'chief') {
            $chiefCounts[$row[8]] = ($chiefCounts[$row[8]] ?? 0) + 1;
        }
    }
    $rows[] = $row;
}

if ($rows === []) {
    fail('no input rows');
}
foreach ($nameIndexes as $generalName => $indexes) {
    if (count($indexes) === 1) {
        continue;
    }
    foreach ($indexes as $index) {
        $rows[$index][0] .= "#{$rows[$index][10]}";
        if (mb_strlen($rows[$index][0]) > 32) {
            fail("disambiguated name exceeds 32 characters: {$rows[$index][0]}");
        }
    }
}
ksort($phaseCounts, SORT_NUMERIC);
$expectedPhases = array_values(array_diff(range(1, 99), EXCLUDED_EVENT_PHASES));
if (array_keys($phaseCounts) !== $expectedPhases) {
    fail('input does not cover every non-event phase from 1 through 99');
}
foreach ($expectedPhases as $phase) {
    if (($chiefCounts[$phase] ?? 0) !== 8) {
        fail("phase {$phase}: expected 8 unification chiefs including the ruler");
    }
}

$payload = [
    'excludedEventPhases' => EXCLUDED_EVENT_PHASES,
    'columns' => OUTPUT_COLUMNS,
    'data' => $rows,
];
$encoded = json_encode(
    $payload,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
) . "\n";

$outputDir = dirname($outputPath);
if (!is_dir($outputDir)) {
    fail("output directory does not exist: {$outputDir}");
}
$tempPath = tempnam($outputDir, basename($outputPath) . '.tmp.');
if ($tempPath === false) {
    fail("could not create temporary output in {$outputDir}");
}
if (file_put_contents($tempPath, $encoded) === false || !rename($tempPath, $outputPath)) {
    @unlink($tempPath);
    fail("could not write output: {$outputPath}");
}

fwrite(
    STDERR,
    sprintf(
        "wrote %d candidates across phases %d-%d (%d hall reasons, %d chief reasons)\n",
        count($rows),
        min(array_keys($phaseCounts)),
        max(array_keys($phaseCounts)),
        $reasonCounts['hall'] ?? 0,
        $reasonCounts['chief'] ?? 0,
    )
);
