<?php

namespace YlsIdeas\FeatureFlags\Contracts;

interface ExpiredFeaturesHandler
{
    public function isExpired(string $feature): void;
}
