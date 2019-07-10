<?php

namespace YlsIdeas\FeatureFlags\Events;

class FeatureAccessing
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
