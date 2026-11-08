<?php

namespace FlareIA\Sdk;

use FlareIA\Sdk\Senders\Sender;

/**
 * Orchestrates sending reports, traces and logs to a FlareIA server.
 * Endpoint paths mirror the ingestion routes in routes/ingest.php.
 */
class Api
{
    public function __construct(
        private Sender $sender,
        private string $baseUrl,
        private string $apiKey,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function sendError(array $payload): void
    {
        $this->sender->post($this->endpoint('/api/v1/errors'), $this->apiKey, $payload);
    }

    /**
     * @param  array<string, mixed>  $resourceSpansEnvelope
     */
    public function sendTrace(array $resourceSpansEnvelope): void
    {
        $this->sender->post($this->endpoint('/api/v1/traces'), $this->apiKey, $resourceSpansEnvelope);
    }

    /**
     * @param  array<string, mixed>  $resourceLogsEnvelope
     */
    public function sendLog(array $resourceLogsEnvelope): void
    {
        $this->sender->post($this->endpoint('/api/v1/logs'), $this->apiKey, $resourceLogsEnvelope);
    }

    private function endpoint(string $path): string
    {
        return rtrim($this->baseUrl, '/').$path;
    }
}
