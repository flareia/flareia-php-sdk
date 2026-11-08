<?php

namespace FlareIA\Sdk\Senders;

use FlareIA\Sdk\Support\SecureBaseUrl;

class CurlSender implements Sender
{
    public function __construct(
        private int $timeout = 2,
    ) {}

    public function post(string $endpoint, string $apiKey, array $payload): void
    {
        $endpointParts = parse_url($endpoint);

        if ($endpointParts !== false && isset($endpointParts['scheme'], $endpointParts['host'])) {
            $origin = $endpointParts['scheme'].'://'.$endpointParts['host'];
            if (isset($endpointParts['port'])) {
                $origin .= ':'.$endpointParts['port'];
            }
            SecureBaseUrl::validate($origin);
        }

        $ch = curl_init($endpoint);

        if ($ch === false) {
            return;
        }

        try {
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'x-api-token: '.$apiKey,
                ],
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
            ]);
            curl_exec($ch);
        } catch (\Throwable) {
            // Monitoring client must not break the app.
        } finally {
            curl_close($ch);
        }
    }
}
