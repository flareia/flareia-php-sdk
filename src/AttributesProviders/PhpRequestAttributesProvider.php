<?php

namespace FlareIA\Sdk\AttributesProviders;

use FlareIA\Sdk\Contracts\RequestAttributesProvider;

/**
 * Default request context for plain PHP (no framework request object
 * available) — reads directly from superglobals.
 */
class PhpRequestAttributesProvider implements RequestAttributesProvider
{
    public function attributes(): array
    {
        if (! isset($_SERVER['REQUEST_METHOD'])) {
            return [];
        }

        $scheme = ($_SERVER['HTTPS'] ?? '') !== '' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        // Same key convention as the Laravel adapter's error `request` field
        // (method/url/ip) — error reports are plain JSON, not OTel-shaped.
        return array_filter([
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'url' => $host !== '' ? "{$scheme}://{$host}{$uri}" : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ], fn ($value) => $value !== null);
    }
}
