<?php

namespace YlsIdeas\FeatureFlags\Gateways;

use Illuminate\Redis\Connections\Connection;
use YlsIdeas\FeatureFlags\Contracts\Cacheable;
use YlsIdeas\FeatureFlags\Contracts\Gateway;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Gateways\RedisGatewayTest
 */
class RedisGateway implements Gateway, Cacheable
{
    public function __construct(protected Connection $connection, protected ?string $prefix = 'features')
    {
    }

    public function accessible(string $feature): ?bool
    {
        return $this->connection->get($this->key($feature));
    }

    public function turnOn(string $feature): void
    {
        $this->connection->set($this->key($feature), true);
    }

    public function turnOff(string $feature): void
    {
        $this->connection->set($this->key($feature), false);
    }

    protected function key(string $key): string
    {
        return implode(':', array_filter([$this->prefix, $key]));
    }

    public function generateKey(string $feature): string
    {
        return md5($feature);
    }
}
