<?php

namespace YlsIdeas\FeatureFlags\Events;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Events\FeatureAccessedTest
 */
class FeatureAccessed
{
    public function __construct(public string $feature, public ?bool $result)
    {
    }
}
