<?php

namespace YlsIdeas\FeatureFlags\Contracts;

interface Features
{
    public function accessible(string $feature): bool;
}
