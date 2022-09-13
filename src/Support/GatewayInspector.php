<?php

namespace YlsIdeas\FeatureFlags\Support;

use YlsIdeas\FeatureFlags\Contracts\ActionableFlag;
use YlsIdeas\FeatureFlags\Contracts\Gateway;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Support\GatewayInspectorTest
 */
class GatewayInspector
{
    public function __construct(
        protected Gateway $gateway,
        protected ?FeatureFilter $filter = null,
        protected ?GatewayCache $cache = null,
    ) {
    }

    public function gateway(): Gateway
    {
        return $this->gateway;
    }

    public function filter(): ?FeatureFilter
    {
        return $this->filter;
    }

    public function cache(): ?GatewayCache
    {
        return $this->cache;
    }

    public function handle(ActionableFlag $action, callable $next): ActionableFlag
    {
        if ($action->hasResult()) {
            return $next($action);
        }

        if ($this->filter && $this->filter->fails($action->feature())) {
            return $next($action);
        }

        if ($this->cache && $this->cache->hits($action->feature())) {
            $action->setResult($this->cache->result($action->feature()));

            return $next($action);
        }

        if (($result = $this->gateway->accessible($action->feature())) !== null) {
            $this->cache?->store($action->feature(), $result);
            $action->setResult($result);
        }

        return $next($action);
    }
}
