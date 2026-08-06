<?php

namespace sammo;

final class ImageSyncClient
{
    /**
     * @return array{body:string,headers:list<string>,requestId:string}
     */
    public static function buildRequest(
        string $client,
        string $secret,
        ?string $commit = null,
        ?int $timestampMs = null,
        ?string $requestId = null
    ): array {
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{1,31}$/', $client)) {
            throw new \InvalidArgumentException('Invalid image sync client');
        }
        if (strlen($secret) < 32) {
            throw new \InvalidArgumentException('Image sync secret must be at least 32 characters');
        }
        if ($commit !== null && !preg_match('/^[0-9a-f]{40,64}$/i', $commit)) {
            throw new \InvalidArgumentException('Image commit must be a full Git SHA');
        }
        $body = Json::encode($commit === null ? (object)[] : ['commit' => $commit]);
        $timestamp = (string)($timestampMs ?? (int)floor(microtime(true) * 1000));
        $requestId ??= bin2hex(random_bytes(16));
        $signature = hash_hmac('sha256', "{$timestamp}.{$requestId}.{$body}", $secret);
        return [
            'body' => $body,
            'requestId' => $requestId,
            'headers' => [
                'Content-Type: application/json',
                "X-Image-Client: {$client}",
                "X-Image-Timestamp: {$timestamp}",
                "X-Image-Request-Id: {$requestId}",
                "X-Image-Signature: {$signature}",
            ],
        ];
    }

    /** @return array<string,mixed> */
    public static function sync(string $url, string $client, string $secret, ?string $commit = null): array
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);
        if ($scheme !== 'https' && !in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
            throw new \InvalidArgumentException('Image sync URL must use HTTPS except for loopback tests');
        }
        $request = self::buildRequest($client, $secret, $commit);
        $curl = curl_init($url);
        if ($curl === false) {
            throw new \RuntimeException('Unable to initialize image sync request');
        }
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $request['headers'],
            CURLOPT_POSTFIELDS => $request['body'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if ($response === false) {
            throw new \RuntimeException("Image sync request failed: {$error}");
        }
        $decoded = Json::decode($response);
        if ($status < 200 || $status >= 300 || !($decoded['ok'] ?? false)) {
            throw new \RuntimeException("Image sync rejected ({$status}): " . ($decoded['reason'] ?? 'unknown error'));
        }
        return $decoded;
    }
}
