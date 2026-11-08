<?php

namespace FlareIA\Sdk\Senders;

class CurlSender implements Sender
{
    public function __construct(
        private int $timeout = 2,
    ) {}

    public function post(string $endpoint, string $apiKey, array $payload): void
    {
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
