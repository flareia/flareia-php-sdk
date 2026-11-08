<?php

namespace FlareIA\Sdk;

/**
 * Identifies the SDK (instrumentation scope) that produced a span or log,
 * e.g. "flareia-php" or "flareia-laravel".
 */
class Scope
{
    public function __construct(
        public string $name,
        public string $version = '0.1.0',
    ) {}
}
