<?php

namespace YlsIdeas\FeatureFlags\Events;

use YlsIdeas\FeatureFlags\Support\ActionDebugLog;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Events\FeatureAccessedTest
 */
class FeatureAccessed
{
    public function __construct(public string $feature, public ?bool $result, public ?ActionDebugLog $log = null)
    {
    }
}
