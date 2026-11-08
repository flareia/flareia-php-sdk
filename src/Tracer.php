<?php

namespace FlareIA\Sdk;

use FlareIA\Sdk\Spans\Span;
use FlareIA\Sdk\Support\OpenTelemetryAttributeMapper;

/**
 * Buffers spans recorded during a request/process and flushes them as a
 * single OTel-shaped trace envelope, mirroring Spatie\FlareClient\Tracer
 * (simplified: no span stack / W3C propagation — recorders hand it
 * already-finished spans built from framework events).
 */
class Tracer
{
    /** @var Span[] */
    private array $spans = [];

    private ?string $traceId = null;

    public function __construct(
        private Api $api,
        private Resource $resource,
        private Scope $scope,
        private OpenTelemetryAttributeMapper $attributeMapper = new OpenTelemetryAttributeMapper,
    ) {}

    public function record(Span $span): void
    {
        $this->spans[] = $span;
    }

    /**
     * One trace id per flush cycle, so every span recorded during the same
     * request/job shares a traceId — lazily generated on first use.
     */
    public function currentTraceId(): string
    {
        return $this->traceId ??= self::newTraceId();
    }

    public static function newTraceId(): string
    {
        return bin2hex(random_bytes(16));
    }

    public static function newSpanId(): string
    {
        return bin2hex(random_bytes(8));
    }

    public function flush(): void
    {
        if ($this->spans === []) {
            return;
        }

        $spans = $this->spans;
        $this->spans = [];
        $this->traceId = null;

        $this->api->sendTrace([
            'resourceSpans' => [[
                'resource' => [
                    'attributes' => $this->attributeMapper->attributesToOpenTelemetry($this->resource->attributes),
                    'droppedAttributesCount' => 0,
                ],
                'scopeSpans' => [[
                    'scope' => [
                        'name' => $this->scope->name,
                        'version' => $this->scope->version,
                        'attributes' => [],
                        'droppedAttributesCount' => 0,
                    ],
                    'spans' => array_map($this->exportSpan(...), $spans),
                ]],
            ]],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function exportSpan(Span $span): array
    {
        return [
            'traceId' => $span->traceId,
            'spanId' => $span->spanId,
            'parentSpanId' => $span->parentSpanId,
            'name' => $span->name,
            'startTimeUnixNano' => $span->startTimeUnixNano,
            'endTimeUnixNano' => $span->endTimeUnixNano,
            'attributes' => $this->attributeMapper->attributesToOpenTelemetry($span->allAttributes()),
            'droppedAttributesCount' => 0,
            'events' => array_map(fn ($event) => [
                'name' => $event->name,
                'timeUnixNano' => $event->timeUnixNano,
                'attributes' => $this->attributeMapper->attributesToOpenTelemetry($event->attributes),
                'droppedAttributesCount' => 0,
            ], $span->events),
            'droppedEventsCount' => 0,
            'links' => [],
            'droppedLinksCount' => 0,
            'status' => ['code' => 0],
        ];
    }
}
