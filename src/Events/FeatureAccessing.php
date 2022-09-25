<?php

namespace YlsIdeas\FeatureFlags\Events;

use YlsIdeas\FeatureFlags\Support\ActionDebugLog;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Events\FeatureAccessingTest
 */
class FeatureAccessing
{
    public function __construct(public string $feature, public ?ActionDebugLog $log = null)
    {
    }
}
