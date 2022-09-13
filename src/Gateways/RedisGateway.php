<?php

namespace YlsIdeas\FeatureFlags\Gateways;

use Illuminate\Redis\Connections\Connection;
use YlsIdeas\FeatureFlags\Contracts\Cacheable;
use YlsIdeas\FeatureFlags\Contracts\Gateway;

class RedisGateway implements Gateway, Cacheable
{
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var string
     */
    protected $prefix;

    public function __construct(Connection $connection, string $prefix = 'features')
    {
        $this->connection = $connection;
        $this->prefix = $prefix;
    }

    public function accessible(string $feature): ?bool
    {
        if (($result = $this->connection->get($this->key($feature))) !== null) {
            return (bool) $result;
        }

        return null;
    }

    /**
     * @param string $feature
     * @return void
     */
    public function turnOn(string $feature): void
    {
        $this->connection->set($this->key($feature), true);
    }

    /**
     * @param string $feature
     * @return void
     */
    public function turnOff(string $feature): void
    {
        $this->connection->set($this->key($feature), false);
    }

    /**
     * @param string $key
     * @return string
     */
    protected function key(string $key): string
    {
        return $this->prefix.':'.$key;
    }

    public function generateKey(string $feature): string
    {
        return md5($feature);
    }
}
