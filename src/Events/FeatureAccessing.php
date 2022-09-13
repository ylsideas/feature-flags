<?php

namespace YlsIdeas\FeatureFlags\Events;

class FeatureAccessing
{
    public function __construct(public string $feature)
    {
    }
}
