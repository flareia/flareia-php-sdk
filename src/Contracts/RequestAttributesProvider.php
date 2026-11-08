<?php

namespace FlareIA\Sdk\Contracts;

interface RequestAttributesProvider
{
    /**
     * @return array<string, mixed>
     */
    public function attributes(): array;
}
