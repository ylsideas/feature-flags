<?php

namespace YlsIdeas\FeatureFlags\Gateways;

use YlsIdeas\FeatureFlags\Contracts\Cacheable;
use YlsIdeas\FeatureFlags\Contracts\Gateway;
use YlsIdeas\FeatureFlags\Contracts\InMemoryLoader;

class InMemoryGateway implements Gateway, Cacheable
{
    protected array $flags;

    public function __construct(InMemoryLoader $loader)
    {
        $this->flags = $loader->load();
    }

    public function accessible(string $feature): ?bool
    {
        if (($result = data_get($this->flags, $feature)) !== null) {
            return (bool) $result;
        }

        return null;
    }

    public function generateKey(string $feature): string
    {
        return md5($feature);
    }
}
