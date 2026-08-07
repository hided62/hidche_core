<?php

use PHPUnit\Framework\TestCase;
use sammo\RemoteImageUploadException;
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

    public function testLogFailureWritesSafeOperatorEntryWithoutTraceOrSecret(): void
    {
        $secret = 'secret-' . str_repeat('z', 64);
        $systemMessages = [];
        $structuredEntries = [];

        RemoteUserIconUploadClient::logFailure(
            'user-icon',
            new RuntimeException("request failed with {$secret}"),
            static function (string $message) use (&$systemMessages): void {
                $systemMessages[] = $message;
            },
            static function (string $type, string $message, string $path, array $trace) use (&$structuredEntries): void {
                $structuredEntries[] = compact('type', 'message', 'path', 'trace');
            }
        );

        self::assertSame(['Remote user icon upload failed: Unexpected RuntimeException'], $systemMessages);
        self::assertCount(1, $structuredEntries);
        self::assertSame('RemoteImageUploadFailure', $structuredEntries[0]['type']);
        self::assertSame($systemMessages[0], $structuredEntries[0]['message']);
        self::assertSame([], $structuredEntries[0]['trace']);
        self::assertStringNotContainsString($secret, json_encode($structuredEntries, JSON_THROW_ON_ERROR));
    }

    public function testLogFailureKeepsOnlyClientGeneratedSafeReason(): void
    {
        $structuredEntries = [];
        RemoteUserIconUploadClient::logFailure(
            'content-image',
            new RemoteImageUploadException('Image upload rejected (401)'),
            static function (): void {},
            static function (string $type, string $message, string $path, array $trace) use (&$structuredEntries): void {
                $structuredEntries[] = compact('type', 'message', 'path', 'trace');
            }
        );

        self::assertSame(
            'Remote content image upload failed: Image upload rejected (401)',
            $structuredEntries[0]['message']
        );
        self::assertSame([], $structuredEntries[0]['trace']);
    }

    public function testLogFailureRejectsAnUnapprovedClientExceptionMessage(): void
    {
        $secret = 'secret-' . str_repeat('q', 64);
        $structuredEntries = [];
        RemoteUserIconUploadClient::logFailure(
            'user-icon',
            new RemoteImageUploadException("unexpected response {$secret}"),
            static function (): void {},
            static function (string $type, string $message, string $path, array $trace) use (&$structuredEntries): void {
                $structuredEntries[] = compact('type', 'message', 'path', 'trace');
            }
        );

        self::assertSame(
            'Remote user icon upload failed: Unexpected sammo\\RemoteImageUploadException',
            $structuredEntries[0]['message']
        );
        self::assertStringNotContainsString($secret, json_encode($structuredEntries, JSON_THROW_ON_ERROR));
    }
}
