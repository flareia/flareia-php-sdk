<?php

namespace FlareIA\Sdk;

/**
 * Identifies the reporting application in the OpenTelemetry sense
 * (the OTel "Resource" — what produced the telemetry).
 */
class Resource
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public array $attributes = [],
    ) {}

    public static function make(?string $environment, ?string $release, string $sdkLanguage): self
    {
        return new self(array_filter([
            'service.name' => $environment,
            'service.version' => $release,
            'telemetry.sdk.name' => 'flareia',
            'telemetry.sdk.language' => $sdkLanguage,
        ], fn ($value) => $value !== null));
    }
}
