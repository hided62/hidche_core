<?php

use PHPUnit\Framework\TestCase;
use sammo\ImageSyncClient;

require_once dirname(__DIR__) . '/src/sammo/ImageSyncClient.php';

final class ImageSyncClientTest extends TestCase
{
    public function testBuildRequestSignsTheExactBody(): void
    {
        $secret = str_repeat('c', 32);
        $request = ImageSyncClient::buildRequest(
            'core',
            $secret,
            str_repeat('a', 40),
            1786013000000,
            'core-request-1234'
        );
        $headers = implode("\n", $request['headers']);
        $expected = hash_hmac(
            'sha256',
            "1786013000000.core-request-1234.{$request['body']}",
            $secret
        );
        self::assertStringContainsString("X-Image-Signature: {$expected}", $headers);
        self::assertSame('{"commit":"' . str_repeat('a', 40) . '"}', $request['body']);
    }

    public function testBuildRequestRejectsAbbreviatedCommit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ImageSyncClient::buildRequest('core', str_repeat('c', 32), 'deadbeef');
    }
}
