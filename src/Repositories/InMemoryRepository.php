<?php

namespace YlsIdeas\FeatureFlags\Repositories;

use YlsIdeas\FeatureFlags\Contracts\Repository;

class InMemoryRepository implements Repository
{
    /**
     * @var array
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = collect($config)
            ->map(function ($item) {
                return (bool) value($item);
            })
            ->toArray();
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
