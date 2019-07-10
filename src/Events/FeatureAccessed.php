<?php

namespace YlsIdeas\FeatureFlags\Events;

class FeatureAccessed
{
    /**
     * @var string
     */
    public $feature;

    /**
     * Create a new event instance.
     *
     * @param string $feature
     */
    public function __construct(string $feature)
    {
        $this->feature = $feature;
    }
}
