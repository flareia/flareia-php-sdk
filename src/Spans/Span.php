<?php

namespace FlareIA\Sdk\Spans;

/**
 * A single unit of work in a trace. The span "type" (route/query/job/...)
 * is carried as the `tracker.span_type` attribute rather than a top-level
 * field, mirroring Flare's `flare.span_type` convention.
 */
class Span
{
    /** @var SpanEvent[] */
    public array $events = [];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public string $traceId,
        public string $spanId,
        public ?string $parentSpanId,
        public string $name,
        public string $type,
        public int $startTimeUnixNano,
        public ?int $endTimeUnixNano,
        public array $attributes = [],
    ) {}

    public function addEvent(SpanEvent $event): self
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function allAttributes(): array
    {
        return ['tracker.span_type' => $this->type] + $this->attributes;
    }
}
