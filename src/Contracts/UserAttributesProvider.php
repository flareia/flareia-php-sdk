<?php

namespace FlareIA\Sdk\Contracts;

interface UserAttributesProvider
{
    /**
     * @return array<string, mixed>
     */
    public function attributes(): array;
}
