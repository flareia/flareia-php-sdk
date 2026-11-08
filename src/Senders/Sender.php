<?php

namespace FlareIA\Sdk\Senders;

interface Sender
{
    /**
     * Post a JSON payload to an ingestion endpoint. Implementations must
     * never throw — a monitoring client must not be able to take down the
     * application it is monitoring.
     *
     * @param  array<string, mixed>  $payload
     */
    public function post(string $endpoint, string $apiKey, array $payload): void;
}
