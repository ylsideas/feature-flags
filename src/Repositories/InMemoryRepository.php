<?php

namespace YlsIdeas\FeatureFlags\Repositories;

use Illuminate\Support\Arr;
use YlsIdeas\FeatureFlags\Contracts\Repository;

class InMemoryRepository implements Repository
{
    /**
     * @var array
     */
    protected $config;

    public function __construct(array $config)
    {
        foreach (Arr::dot($config) as $key => $value) {
            data_set($this->config, $key, (bool) value($value));
        }
    }

    public function accessible(string $feature)
    {
        if (($result = data_get($this->config, $feature)) !== null) {
            return (bool) $result;
        }
    }

    /**
     * @return array<string, bool>
     */
    public function all()
    {
        return $this->config;
    }

    public function turnOn(string $feature)
    {
        data_set($this->config, $feature, true);
    }

    public function turnOff(string $feature)
    {
        data_set($this->config, $feature, false);
    }
}
