<?php

namespace YlsIdeas\FeatureFlags\Events;

class FeatureSwitchedOff
{
    public function __construct(public string $feature, public string $gateway)
    {
    }
}
