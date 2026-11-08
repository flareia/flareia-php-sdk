<?php

namespace FlareIA\Sdk\Support;

final class SecureBaseUrl
{
    private const RESTRICTED_HOSTS = [
        'metadata.google.internal',
        'metadata.google.internal.',
    ];

    /**
     * @throws \InvalidArgumentException
     */
    public static function validate(string $baseUrl): string
    {
        $trimmed = trim($baseUrl);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('FlareIA base URL cannot be empty.');
        }

        $parts = parse_url($trimmed);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            throw new \InvalidArgumentException('FlareIA base URL must be a valid absolute URL.');
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new \InvalidArgumentException('FlareIA base URL must use http or https.');
        }

        if ($scheme === 'http' && ! self::isLocalHost($host)) {
            throw new \InvalidArgumentException('HTTP is only allowed for localhost base URLs.');
        }

        if (self::isRestrictedHost($host)) {
            throw new \InvalidArgumentException('FlareIA base URL points to a restricted host.');
        }

        return rtrim($trimmed, '/');
    }

    public static function isLocalHost(string $host): bool
    {
        return in_array($host, ['localhost', '127.0.0.1', '[::1]', '::1'], true);
    }

    private static function isRestrictedHost(string $host): bool
    {
        if (in_array($host, self::RESTRICTED_HOSTS, true)) {
            return true;
        }

        if ($host === '169.254.169.254' || $host === '169.254.170.2') {
            return true;
        }

        return false;
    }
}
