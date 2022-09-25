<?php

namespace YlsIdeas\FeatureFlags;

use YlsIdeas\FeatureFlags\Contracts\ExpiredFeaturesHandler as ExpiredFeaturesHandlerContract;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\ExpiredFeaturesHandlerTest
 */
class ExpiredFeaturesHandler implements ExpiredFeaturesHandlerContract
{
    /**
     * @var callable
     */
    protected mixed $handler;

    public function __construct(protected array $features, callable $handler)
    {
        $this->handler = $handler;
    }

    public function isExpired(string $feature): void
    {
        if (in_array($feature, $this->features)) {
            call_user_func($this->handler, $feature);
        }
    }
}
