<?php

namespace FlareIA\Sdk\Support;

/**
 * Maps between our free-form `level` strings (debug/info/warning/error/...)
 * and the OpenTelemetry severityNumber/severityText scale, symmetrically
 * with App\Support\OpenTelemetry\SeverityMapper on the ingestion side.
 */
class SeverityMapper
{
    /** @var array<string, int> */
    private const LEVEL_TO_NUMBER = [
        'trace' => 1,
        'debug' => 5,
        'info' => 9,
        'notice' => 10,
        'warning' => 13,
        'warn' => 13,
        'error' => 17,
        'critical' => 18,
        'alert' => 19,
        'emergency' => 21,
        'fatal' => 21,
    ];

    /** @var array<int, string> */
    private const NUMBER_TO_LEVEL = [
        1 => 'trace',
        5 => 'debug',
        9 => 'info',
        10 => 'notice',
        13 => 'warning',
        17 => 'error',
        18 => 'critical',
        19 => 'alert',
        21 => 'emergency',
    ];

    public function toSeverityNumber(string $level): int
    {
        return self::LEVEL_TO_NUMBER[strtolower($level)] ?? 9;
    }

    public function toSeverityText(string $level): string
    {
        return strtoupper($level);
    }

    public function fromSeverityNumber(int $severityNumber): string
    {
        if (array_key_exists($severityNumber, self::NUMBER_TO_LEVEL)) {
            return self::NUMBER_TO_LEVEL[$severityNumber];
        }

        return match (true) {
            $severityNumber >= 21 => 'critical',
            $severityNumber >= 17 => 'error',
            $severityNumber >= 13 => 'warning',
            $severityNumber >= 9 => 'info',
            default => 'debug',
        };
    }
}
