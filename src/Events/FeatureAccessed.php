<?php

namespace YlsIdeas\FeatureFlags\Events;

class FeatureAccessed
{
    public function __construct(public string $feature, public ?bool $result)
    {
    }
}
