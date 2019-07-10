<?php

namespace YlsIdeas\FeatureFlags;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Manager as BaseManager;
use YlsIdeas\FeatureFlags\Contracts\Repository;
use Illuminate\Contracts\Foundation\Application;
use YlsIdeas\FeatureFlags\Repositories\ChainRepository;
use YlsIdeas\FeatureFlags\Repositories\RedisRepository;
use YlsIdeas\FeatureFlags\Controllers\FeaturesController;
use YlsIdeas\FeatureFlags\Repositories\DatabaseRepository;
use YlsIdeas\FeatureFlags\Repositories\InMemoryRepository;
use Illuminate\Contracts\Container\BindingResolutionException;

class Manager extends BaseManager implements Repository
{
    /**
     * @var bool
     */
    protected $useCommands = true;
    /**
     * @var bool
     */
    protected $useBlade = true;
    /**
     * @var bool
     */
    protected $useValidations = true;
    /**
     * @var bool
     */
    protected $useScheduling = true;

    /** @var Dispatcher */
    protected $dispatcher;

    /**
     * Manager constructor.
     * @param Application $app
     * @throws BindingResolutionException
     */
    public function __construct(Application $app, Dispatcher $dispatcher)
    {
        parent::__construct($app);
        $this->dispatcher = $dispatcher;
    }

    public function routes($path = 'features', $router = null)
    {
        $router = $router ?? $this->app->make('router');
        $router->get(
            $path,
            FeaturesController::class
        )
            ->name('features');
    }

    /**
     * Get the default driver name.
     *
     * @throws BindingResolutionException
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app
            ->make(\Illuminate\Contracts\Config\Repository::class)
            ->get('features.default');
    }

    /**
     * @param string $feature
     * @return bool
     */
    public function accessible(string $feature)
    {
        $this->dispatcher->dispatch(new Events\FeatureAccessing($feature));
        $result = $this->driver()->accessible($feature) ?? false;
        if ($result) {
            $this->dispatcher->dispatch(new Events\FeatureAccessed($feature));
        }

        return $result;
    }

    /**
     * @return array<string, bool>
     */
    public function all()
    {
        return $this->driver()->all();
    }

    /**
     * @param string $feature
     */
    public function turnOn(string $feature)
    {
        $this->driver()->turnOn($feature);
        $this->dispatcher->dispatch(new Events\FeatureSwitchedOn($feature));
    }

    /**
     * @param string $feature
     */
    public function turnOff(string $feature)
    {
        $this->driver()->turnOff($feature);
        $this->dispatcher->dispatch(new Events\FeatureSwitchedOff($feature));
    }

    /**
     * @return Repository
     * @throws BindingResolutionException
     */
    protected function createConfigDriver()
    {
        return $this->app->make(InMemoryRepository::class);
    }

    /**
     * @return Repository
     * @throws BindingResolutionException
     */
    protected function createRedisDriver()
    {
        return $this->app->make(RedisRepository::class);
    }

    /**
     * @return Repository
     * @throws BindingResolutionException
     */
    protected function createDatabaseDriver()
    {
        return $this->app->make(DatabaseRepository::class);
    }

    /**
     * @return Repository
     * @throws BindingResolutionException
     */
    protected function createChainDriver()
    {
        return $this->app->make(ChainRepository::class);
    }

    public function noBlade()
    {
        $this->useBlade = false;

        return $this;
    }

    public function noScheduling()
    {
        $this->useScheduling = false;

        return $this;
    }

    public function noValidations()
    {
        $this->useValidations = false;

        return $this;
    }

    public function noCommands()
    {
        $this->useCommands = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function usesBlade(): bool
    {
        return $this->useBlade;
    }

    /**
     * @return bool
     */
    public function usesValidations(): bool
    {
        return $this->useValidations;
    }

    /**
     * @return bool
     */
    public function usesScheduling(): bool
    {
        return $this->useScheduling;
    }

    /**
     * @return bool
     */
    public function usesCommands(): bool
    {
        return $this->useCommands;
    }
}
