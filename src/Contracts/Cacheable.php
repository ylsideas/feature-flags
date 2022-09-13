<?php

namespace YlsIdeas\FeatureFlags\Contracts;

interface Cacheable
{
    public function generateKey(string $feature): string;
}
