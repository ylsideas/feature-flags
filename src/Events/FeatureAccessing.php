<?php

namespace YlsIdeas\FeatureFlags\Events;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Events\FeatureAccessingTest
 */
class FeatureAccessing
{
    public function __construct(public string $feature)
    {
    }
}
