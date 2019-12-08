<?php

namespace YlsIdeas\FeatureFlags\Repositories;

use YlsIdeas\FeatureFlags\Contracts\Repository;
use YlsIdeas\FeatureFlags\Manager;

class ChainRepository implements Repository
{
    /**
     * @var string[]
     */
    protected $repositories;
    /**
     * @var Manager
     */
    protected $manager;
    /**
     * @var string
     */
    protected $stateDriver;
    /**
     * @var bool
     */
    protected $updateOnResolve;

    /**
     * @param Manager $manager
     * @param string[] $repositories
     * @param string $stateDriver
     * @param bool $updateOnResolve
     */
    public function __construct(
        Manager $manager,
        array $repositories,
        string $stateDriver = null,
        bool $updateOnResolve = false
    ) {
        if (count($repositories) < 1) {
            throw new \InvalidArgumentException('One repository must be supplied for $repositories');
        }
        $this->manager = $manager;
        $this->repositories = $repositories;
        $this->stateDriver = $stateDriver ?? $repositories[0];
        $this->updateOnResolve = $updateOnResolve;
    }

    public function accessible(string $feature)
    {
        $attemptedDrivers = [];
        foreach ($this->repositories as $driver) {
            if (($result = $this->manager->driver($driver)->accessible($feature)) !== null) {
                if ($this->updateOnResolve) {
                    foreach ($attemptedDrivers as $attemptedDriver) {
                        call_user_func(
                            [$this->manager->driver($attemptedDriver), $result ? 'turnOn' : 'turnOff'],
                            $feature
                        );
                    }
                }

                return $result;
            }
            $attemptedDrivers[] = $driver;
        }
    }

    /**
     * @return array<string, bool>
     */
    public function all()
    {
        $features = collect();
        foreach ($this->repositories as $driver) {
            $features = $features->merge($this->manager->driver($driver)->all());
        }

        return $features->toArray();
    }

    /**
     * @param string $feature
     * @return void
     */
    public function turnOn(string $feature)
    {
        $this->manager->driver($this->stateDriver)->turnOn($feature);
    }

    /**
     * @param string $feature
     * @return void
     */
    public function turnOff(string $feature)
    {
        $this->manager->driver($this->stateDriver)->turnOff($feature);
    }
}
