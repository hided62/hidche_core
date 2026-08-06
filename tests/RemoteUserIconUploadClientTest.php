<?php

use PHPUnit\Framework\TestCase;
use sammo\RemoteUserIconUploadClient;

require_once dirname(__DIR__) . '/src/sammo/RemoteUserIconUploadClient.php';

final class RemoteUserIconUploadClientTest extends TestCase
{
    public function testBuildRequestBindsExpiryPathContentTypeAndBody(): void
    {
        $secret = str_repeat('u', 32);
        $body = "\x89PNG\r\n\x1a\nbody";
        $url = 'https://sam-image.hided.net/v1/uploads/user-icons/core/' . str_repeat('a', 32) . '.png';
        $request = RemoteUserIconUploadClient::buildRequest(
            $url,
            'core',
            $secret,
            'image/png',
            $body,
            1786012860,
            'core-upload-1234'
        );
        $expected = hash_hmac(
            'sha256',
            '1786012860.core-upload-1234./v1/uploads/user-icons/core/' . str_repeat('a', 32)
                . '.png.image/png.' . hash('sha256', $body),
            $secret
        );
        self::assertStringContainsString("X-Image-Signature: {$expected}", implode("\n", $request['headers']));
        self::assertStringNotContainsString($secret, implode("\n", $request['headers']));
    }

    public function testBuildRequestRejectsAPathOutsideTheCallerScope(): void
    {
        $this->expectException(InvalidArgumentException::class);
        RemoteUserIconUploadClient::buildRequest(
            'https://sam-image.hided.net/v1/uploads/user-icons/core2026/' . str_repeat('a', 32) . '.png',
            'core',
            str_repeat('u', 32),
            'image/png',
            'body'
        );
    }

    public function testBuildRequestAcceptsScopedEditorContent(): void
    {
        $request = RemoteUserIconUploadClient::buildRequest(
            'https://sam-image.hided.net/v1/uploads/content/core/' . str_repeat('b', 32) . '.jpeg',
            'core',
            str_repeat('u', 32),
            'image/jpeg',
            "\xff\xd8\xffbody",
            1786012860,
            'core-content-1234'
        );
        self::assertStringContainsString('X-Image-Client: core', implode("\n", $request['headers']));
    }
}
