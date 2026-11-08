<?php

namespace FlareIA\Sdk\Support;

/**
 * Encodes/decodes attributes using the OpenTelemetry typed-value convention
 * ({"key": "...", "value": {"stringValue": "..."}}), the same scheme Flare
 * uses (Spatie\FlareClient\Support\OpenTelemetryAttributeMapper).
 */
class OpenTelemetryAttributeMapper
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return list<array{key: string, value: array<string, mixed>}>
     */
    public function attributesToOpenTelemetry(array $attributes): array
    {
        $result = [];

        foreach ($attributes as $key => $value) {
            $mapped = $this->valueToOpenTelemetry($value);

            if ($mapped === null) {
                continue;
            }

            $result[] = ['key' => (string) $key, 'value' => $mapped];
        }

        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function valueToOpenTelemetry(mixed $value): ?array
    {
        if (is_string($value)) {
            return ['stringValue' => $value];
        }

        if (is_bool($value)) {
            return ['boolValue' => $value];
        }

        if (is_int($value)) {
            return ['intValue' => $value];
        }

        if (is_float($value)) {
            return ['doubleValue' => $value];
        }

        if ($value === null) {
            return null;
        }

        if (is_array($value) && array_is_list($value)) {
            return [
                'arrayValue' => [
                    'values' => array_values(array_map(
                        fn (mixed $item) => $this->valueToOpenTelemetry($item),
                        $value,
                    )),
                ],
            ];
        }

        if (is_array($value)) {
            return [
                'kvlistValue' => [
                    'values' => array_map(
                        fn ($item, $key) => ['key' => (string) $key, 'value' => $this->valueToOpenTelemetry($item)],
                        $value,
                        array_keys($value),
                    ),
                ],
            ];
        }

        if ($value instanceof \BackedEnum) {
            return $this->valueToOpenTelemetry($value->value);
        }

        return ['stringValue' => json_encode($value)];
    }

    /**
     * @param  list<array{key?: string, value?: array<string, mixed>}>  $attributes
     * @return array<string, mixed>
     */
    public function attributesToPHP(array $attributes): array
    {
        $out = [];

        foreach ($attributes as $attribute) {
            $value = $this->valueToPHP($attribute['value'] ?? null);

            if ($value === null) {
                continue;
            }

            $out[$attribute['key'] ?? ''] = $value;
        }

        return $out;
    }

    public function valueToPHP(?array $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (array_key_exists('stringValue', $value)) {
            return $value['stringValue'];
        }

        if (array_key_exists('boolValue', $value)) {
            return $value['boolValue'];
        }

        if (array_key_exists('intValue', $value)) {
            return $value['intValue'];
        }

        if (array_key_exists('doubleValue', $value)) {
            return $value['doubleValue'];
        }

        if (array_key_exists('kvlistValue', $value)) {
            $out = [];

            foreach ($value['kvlistValue']['values'] ?? [] as $item) {
                $out[$item['key'] ?? ''] = $this->valueToPHP($item['value'] ?? null);
            }

            return $out;
        }

        if (array_key_exists('arrayValue', $value)) {
            return array_map(
                fn ($item) => $this->valueToPHP($item),
                $value['arrayValue']['values'] ?? [],
            );
        }

        return null;
    }
}
