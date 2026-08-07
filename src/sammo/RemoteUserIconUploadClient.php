<?php

namespace sammo;

final class RemoteImageUploadException extends \RuntimeException
{
}

final class RemoteUserIconUploadClient
{
    private const SAFE_EXACT_ERRORS = [
        'Invalid image upload client',
        'Image upload secret must be at least 32 characters',
        'Invalid image upload URL',
        'Image upload URL must use HTTPS except for loopback tests',
        'Remote user icon upload is not configured',
        'Remote user icon upload secret file is not configured',
        'Remote user icon upload secret file cannot be read',
        'Remote user icon upload secret is too short',
        'Unable to initialize image upload request',
        'Image upload returned invalid JSON',
        'Image upload returned an unsuccessful response',
        'Image upload returned an unexpected path',
    ];

    public static function isConfiguredEnabled(): bool
    {
        return property_exists(ServConfig::class, 'remoteUserIconUploadEnabled')
            && ServConfig::$remoteUserIconUploadEnabled === true;
    }

    /** @return array<string,mixed> */
    public static function uploadConfigured(string $filename, string $contentType, string $body): array
    {
        [$baseUrl, $secret] = self::configuredBaseUrlAndSecret();
        return self::upload(
            "{$baseUrl}/v1/uploads/user-icons/core/{$filename}",
            'core',
            $secret,
            $contentType,
            $body
        );
    }

    /** @return array<string,mixed> */
    public static function uploadContentConfigured(string $filename, string $contentType, string $body): array
    {
        [$baseUrl, $secret] = self::configuredBaseUrlAndSecret();
        return self::upload(
            "{$baseUrl}/v1/uploads/content/core/{$filename}",
            'core',
            $secret,
            $contentType,
            $body
        );
    }

    public static function getConfiguredContentPublicUrl(string $filename): string
    {
        [$baseUrl] = self::configuredBaseUrlAndSecret();
        return "{$baseUrl}/uploads/core/{$filename}";
    }

    /**
     * Record a caught upload failure in both the PHP service log and the
     * operator-facing SQLite log without persisting request arguments, response
     * bodies, headers, or secret values.
     */
    public static function logFailure(
        string $operation,
        \Throwable $error,
        ?callable $systemLogger = null,
        ?callable $structuredLogger = null
    ): void {
        $label = match ($operation) {
            'user-icon' => 'Remote user icon upload',
            'content-image' => 'Remote content image upload',
            default => 'Remote image upload',
        };
        $reason = self::safeFailureReason($error);
        $message = "{$label} failed: {$reason}";

        if ($systemLogger === null) {
            error_log($message);
        } else {
            $systemLogger($message);
        }

        try {
            $arguments = [
                'RemoteImageUploadFailure',
                $message,
                $error->getFile() . ':' . $error->getLine(),
                [],
            ];
            if ($structuredLogger === null) {
                logError(...$arguments);
            } else {
                $structuredLogger(...$arguments);
            }
        } catch (\Throwable $loggingError) {
            error_log('Remote image upload structured logging failed: ' . get_debug_type($loggingError));
        }
    }

    private static function safeFailureReason(\Throwable $error): string
    {
        if ($error instanceof RemoteImageUploadException || $error instanceof \InvalidArgumentException) {
            $message = $error->getMessage();
            if (in_array($message, self::SAFE_EXACT_ERRORS, true)
                || ($error instanceof RemoteImageUploadException
                    && preg_match('/^(?:Image upload rejected \([1-5][0-9]{2}\)|Image upload request failed \(cURL [0-9]+\))$/D', $message))) {
                return $message;
            }
        }
        return 'Unexpected ' . get_debug_type($error);
    }

    /** @return array{string,string} */
    private static function configuredBaseUrlAndSecret(): array
    {
        if (!self::isConfiguredEnabled()
            || !property_exists(ServConfig::class, 'remoteUserIconUploadPath')
            || !property_exists(ServConfig::class, 'remoteUserIconUploadSecretFile')) {
            throw new RemoteImageUploadException('Remote user icon upload is not configured');
        }
        $secretPath = ServConfig::$remoteUserIconUploadSecretFile;
        if (!is_string($secretPath) || $secretPath === '' || str_contains($secretPath, "\0")) {
            throw new RemoteImageUploadException('Remote user icon upload secret file is not configured');
        }
        if ($secretPath[0] !== '/') {
            $secretPath = ROOT . '/' . $secretPath;
        }
        $secretContents = @file_get_contents($secretPath);
        if ($secretContents === false) {
            throw new RemoteImageUploadException('Remote user icon upload secret file cannot be read');
        }
        $secret = trim($secretContents);
        if (strlen($secret) < 32) {
            throw new RemoteImageUploadException('Remote user icon upload secret is too short');
        }
        return [rtrim((string)ServConfig::$remoteUserIconUploadPath, '/'), $secret];
    }

    /** @return array{headers:list<string>,requestId:string,expires:string} */
    public static function buildRequest(
        string $url,
        string $client,
        string $secret,
        string $contentType,
        string $body,
        ?int $expires = null,
        ?string $requestId = null
    ): array {
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{1,31}$/', $client)) {
            throw new \InvalidArgumentException('Invalid image upload client');
        }
        if (strlen($secret) < 32) {
            throw new \InvalidArgumentException('Image upload secret must be at least 32 characters');
        }
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || !preg_match('#^/v1/uploads/(?:user-icons|content)/' . preg_quote($client, '#') . '/[a-f0-9]{32}\.(?:avif|webp|jpe?g|png|gif)$#', $path)) {
            throw new \InvalidArgumentException('Invalid image upload URL');
        }
        $expiresText = (string)($expires ?? time() + 60);
        $requestId ??= bin2hex(random_bytes(16));
        $digest = hash('sha256', $body);
        $signature = hash_hmac(
            'sha256',
            "{$expiresText}.{$requestId}.{$path}.{$contentType}.{$digest}",
            $secret
        );
        return [
            'requestId' => $requestId,
            'expires' => $expiresText,
            'headers' => [
                "Content-Type: {$contentType}",
                "X-Image-Client: {$client}",
                "X-Image-Expires: {$expiresText}",
                "X-Image-Request-Id: {$requestId}",
                "X-Image-Signature: {$signature}",
            ],
        ];
    }

    /** @return array<string,mixed> */
    public static function upload(string $url, string $client, string $secret, string $contentType, string $body): array
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);
        if ($scheme !== 'https' && !in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
            throw new \InvalidArgumentException('Image upload URL must use HTTPS except for loopback tests');
        }
        $request = self::buildRequest($url, $client, $secret, $contentType, $body);
        $curl = curl_init($url);
        if ($curl === false) {
            throw new RemoteImageUploadException('Unable to initialize image upload request');
        }
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => $request['headers'],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $curlErrorNumber = curl_errno($curl);
        curl_close($curl);
        if ($response === false) {
            throw new RemoteImageUploadException("Image upload request failed (cURL {$curlErrorNumber})");
        }
        if ($status < 200 || $status >= 300) {
            throw new RemoteImageUploadException("Image upload rejected ({$status})");
        }
        try {
            $decoded = Json::decode($response);
        } catch (\Throwable $error) {
            throw new RemoteImageUploadException('Image upload returned invalid JSON', previous: $error);
        }
        if (!($decoded['ok'] ?? false)) {
            throw new RemoteImageUploadException('Image upload returned an unsuccessful response');
        }
        $filename = basename((string)parse_url($url, PHP_URL_PATH));
        $category = str_contains((string)parse_url($url, PHP_URL_PATH), '/content/') ? 'content' : 'user-icons';
        $expectedPath = $category === 'content'
            ? "uploads/{$client}/{$filename}"
            : "icons/users/{$client}/{$filename}";
        if (($decoded['path'] ?? null) !== $expectedPath) {
            throw new RemoteImageUploadException('Image upload returned an unexpected path');
        }
        return $decoded;
    }
}
