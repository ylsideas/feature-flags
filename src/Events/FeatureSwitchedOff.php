<?php

namespace YlsIdeas\FeatureFlags\Events;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Events\FeatureSwitchedOffTest
 */
class FeatureSwitchedOff
{
    public function __construct(public string $feature, public string $gateway)
    {
    }
}
