<?php

namespace YlsIdeas\FeatureFlags\Contracts;

interface InMemoryLoader
{
    public function load(): array;
}
