<?php

namespace FlareIA\Sdk\Spans;

class SpanEvent
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public string $name,
        public int $timeUnixNano,
        public array $attributes = [],
    ) {}
}
