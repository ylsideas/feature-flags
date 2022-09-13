<?php

namespace YlsIdeas\FeatureFlags\Contracts;

interface Gateway
{
    public function accessible(string $feature): ?bool;
}
