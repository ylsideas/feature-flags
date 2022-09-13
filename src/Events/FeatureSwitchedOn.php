<?php

namespace YlsIdeas\FeatureFlags\Events;

class FeatureSwitchedOn
{
    public function __construct(public string $feature, public string $gateway)
    {
    }
}
