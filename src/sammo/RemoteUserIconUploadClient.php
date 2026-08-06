<?php

namespace sammo;

final class RemoteUserIconUploadClient
{
    public static function isConfiguredEnabled(): bool
    {
        return property_exists(ServConfig::class, 'remoteUserIconUploadEnabled')
            && ServConfig::$remoteUserIconUploadEnabled === true;
    }

    /** @return array<string,mixed> */
    public static function uploadConfigured(string $filename, string $contentType, string $body): array
    {
        if (!self::isConfiguredEnabled()
            || !property_exists(ServConfig::class, 'remoteUserIconUploadPath')
            || !property_exists(ServConfig::class, 'remoteUserIconUploadSecretFile')) {
            throw new \RuntimeException('Remote user icon upload is not configured');
        }
        $secretPath = ServConfig::$remoteUserIconUploadSecretFile;
        if (!is_string($secretPath) || $secretPath === '' || str_contains($secretPath, "\0")) {
            throw new \RuntimeException('Remote user icon upload secret file is not configured');
        }
        if ($secretPath[0] !== '/') {
            $secretPath = ROOT . '/' . $secretPath;
        }
        $secret = trim((string)file_get_contents($secretPath));
        return self::upload(
            rtrim((string)ServConfig::$remoteUserIconUploadPath, '/')
                . '/v1/uploads/user-icons/core/' . $filename,
            'core',
            $secret,
            $contentType,
            $body
        );
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
        if (!is_string($path) || !preg_match('#^/v1/uploads/user-icons/' . preg_quote($client, '#') . '/[a-f0-9]{32}\.(?:avif|webp|jpg|png|gif)$#', $path)) {
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
            throw new \RuntimeException('Unable to initialize image upload request');
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
        $error = curl_error($curl);
        curl_close($curl);
        if ($response === false) {
            throw new \RuntimeException("Image upload request failed: {$error}");
        }
        $decoded = Json::decode($response);
        if ($status < 200 || $status >= 300 || !($decoded['ok'] ?? false)) {
            throw new \RuntimeException("Image upload rejected ({$status}): " . ($decoded['reason'] ?? 'unknown error'));
        }
        $filename = basename((string)parse_url($url, PHP_URL_PATH));
        if (($decoded['path'] ?? null) !== "icons/users/{$client}/{$filename}") {
            throw new \RuntimeException('Image upload returned an unexpected path');
        }
        return $decoded;
    }
}
