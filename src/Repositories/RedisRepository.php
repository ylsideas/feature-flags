<?php

namespace YlsIdeas\FeatureFlags\Repositories;

use Illuminate\Support\Str;
use Illuminate\Redis\Connections\Connection;
use YlsIdeas\FeatureFlags\Contracts\Repository;

class RedisRepository implements Repository
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

    /**
     * @param string $feature
     * @return bool|null
     */
    public function accessible(string $feature)
    {
        if (($result = $this->connection->get($this->key($feature))) !== null) {
            return (bool) $result;
        }
    }

    /**
     * @return array
     */
    public function all()
    {
        $keys = $this->connection->keys($this->key('*'));
        $values = $this->connection->mget($keys);

        return collect(array_combine($keys, $values))->mapWithKeys(function ($item, $key) {
            return [$this->removePrefix($key) => (bool) $item];
        })
            ->toArray();
    }

    /**
     * @param string $feature
     * @return void
     */
    public function turnOn(string $feature)
    {
        $this->connection->set($this->key($feature), true);
    }

    /**
     * @param string $feature
     * @return void
     */
    public function turnOff(string $feature)
    {
        $this->connection->set($this->key($feature), false);
    }

    /**
     * @param string $key
     * @return string
     */
    protected function key(string $key)
    {
        return $this->prefix.':'.$key;
    }

    protected function removePrefix(string $key)
    {
        return Str::substr($key, Str::length($this->prefix) + 1);
    }
}
