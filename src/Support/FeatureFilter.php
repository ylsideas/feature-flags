<?php

namespace YlsIdeas\FeatureFlags\Support;

class FeatureFilter
{
    /**
     * @param string[] $rules
     */
    public function __construct(protected array $rules)
    {
    }

    public function fails(string $feature): bool
    {
        return false;
    }
}
