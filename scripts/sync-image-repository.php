<?php

namespace sammo;

require dirname(__DIR__) . '/vendor/autoload.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This command is CLI-only.\n");
    exit(2);
}

$url = getenv('IMAGE_SYNC_URL') ?: 'https://sam-image.hided.net/v1/sync';
$secretFile = getenv('IMAGE_SYNC_SECRET_FILE') ?: '/run/secrets/image_sync_core_secret';
if (!is_file($secretFile)) {
    fwrite(STDERR, "IMAGE_SYNC_SECRET_FILE is not readable.\n");
    exit(2);
}
$secret = trim(file_get_contents($secretFile));
$result = ImageSyncClient::sync($url, 'core', $secret, $argv[1] ?? null);
fwrite(STDOUT, Json::encode($result) . PHP_EOL);
