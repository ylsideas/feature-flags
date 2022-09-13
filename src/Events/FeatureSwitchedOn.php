<?php

namespace YlsIdeas\FeatureFlags\Events;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Events\FeatureSwitchedOnTest
 */
class FeatureSwitchedOn
{
    public function __construct(public string $feature, public string $gateway)
    {
    }
}
