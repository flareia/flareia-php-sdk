<?php

namespace FlareIA\Sdk;

final class FlareIAConfig
{
    public function __construct(
        public readonly string $apiKey,
        public string $baseUrl = 'https://flareia.com.br',
        public string $applicationPath = '',
        public bool $sendLogs = false,
        public bool $useDaemon = false,
        public float $sampleRate = 1.0,
        public ?string $environment = null,
        public ?string $release = null,
    ) {}

    public static function make(string $apiKey): self
    {
        $baseUrl = getenv('FLAREIA_BASE_URL') ?: 'https://flareia.com.br';

        return new self(apiKey: $apiKey, baseUrl: $baseUrl);
    }

    public function useDefaults(): self
    {
        $this->environment ??= getenv('FLAREIA_ENVIRONMENT') ?: 'production';
        $this->release ??= getenv('FLAREIA_RELEASE') ?: null;
        $this->useDaemon = (getenv('FLAREIA_DAEMON_URL') ?: '') !== '';

        return $this;
    }

    public function log(): self
    {
        $this->sendLogs = true;

        return $this;
    }

    public function applicationPath(string $path): self
    {
        $this->applicationPath = rtrim($path, '/');

        return $this;
    }
}
