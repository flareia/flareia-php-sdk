<?php

namespace FlareIA\Sdk\Support;

class PayloadCensor
{
    /**
     * @param  array<string, mixed>  $data
     * @param  string[]  $fields
     * @return array<string, mixed>
     */
    public function censorFields(array $data, array $fields): array
    {
        $lowerFields = array_map('strtolower', $fields);

        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), $lowerFields, true)) {
                $data[$key] = '[redacted]';

                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->censorFields($value, $fields);
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array{body_fields?: string[], headers?: string[], cookies?: bool, session?: bool, client_ips?: bool}  $config
     * @return array<string, mixed>
     */
    public function censorContext(array $context, array $config): array
    {
        if (! empty($config['body_fields'])) {
            $context = $this->censorFields($context, $config['body_fields']);
        }

        if (! empty($config['headers']) && isset($context['headers']) && is_array($context['headers'])) {
            $context['headers'] = $this->censorFields($context['headers'], $config['headers']);
        }

        if (($config['cookies'] ?? false) && isset($context['cookies'])) {
            $context['cookies'] = '[redacted]';
        }

        if (($config['session'] ?? false) && isset($context['session'])) {
            $context['session'] = '[redacted]';
        }

        if (($config['client_ips'] ?? false) && isset($context['ip'])) {
            $context['ip'] = '[redacted]';
        }

        return $context;
    }
}
