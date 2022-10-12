<?php

namespace YlsIdeas\FeatureFlags;

use Illuminate\Auth\AuthManager;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use YlsIdeas\FeatureFlags\Contracts\Cacheable;
use YlsIdeas\FeatureFlags\Contracts\Gateway;
use YlsIdeas\FeatureFlags\Contracts\Toggleable;
use YlsIdeas\FeatureFlags\Events\FeatureAccessed;
use YlsIdeas\FeatureFlags\Events\FeatureAccessing;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOff;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOn;
use YlsIdeas\FeatureFlags\Gateways\DatabaseGateway;
use YlsIdeas\FeatureFlags\Gateways\GateGateway;
use YlsIdeas\FeatureFlags\Gateways\InMemoryGateway;
use YlsIdeas\FeatureFlags\Gateways\RedisGateway;
use YlsIdeas\FeatureFlags\Support\FeatureFilter;
use YlsIdeas\FeatureFlags\Support\FeaturesFileDiscoverer;
use YlsIdeas\FeatureFlags\Support\FileLoader;
use YlsIdeas\FeatureFlags\Support\GatewayCache;
use YlsIdeas\FeatureFlags\Support\GatewayInspector;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\ManagerTest
 */
class Manager
{
    protected bool $useCommands = true;
    protected bool $useBlade = true;
    protected bool $useValidations = true;
    protected bool $useScheduling = true;
    protected bool $useMiddlewares = true;

    protected array $gatewayDrivers = [];

    public function __construct(protected Container $container, protected Dispatcher $dispatcher)
    {
    }

    /**
     * @throws BindingResolutionException
     */
    public function pipeline(): Pipeline
    {
        return (new Pipeline($this->container))
            ->through($this->pipes());
    }

    /**
     * @throws BindingResolutionException
     */
    public function accessible(string $feature): bool
    {
        $flagAction = new ActionableFlag();
        $flagAction->feature = $feature;

        $this->dispatcher->dispatch(new FeatureAccessing($feature));

        /** @var \YlsIdeas\FeatureFlags\Contracts\ActionableFlag $flagAction */
        $flagAction = $this->pipeline()->send($flagAction)->thenReturn();

        $this->dispatcher->dispatch(new FeatureAccessed($feature, $flagAction->getResult()));

        return (bool) $flagAction->getResult();
    }

    public function turnOn(string $gateway, string $feature): void
    {
        $toggleable = $this->resolve($gateway)->gateway();

        if (! $toggleable instanceof Toggleable) {
            throw new \InvalidArgumentException(sprintf(
                'Gateway `%s` is not a toggleable gateway.',
                $gateway
            ));
        }

        $toggleable->turnOn($feature);

        $this->dispatcher->dispatch(new FeatureSwitchedOn($feature, $gateway));
    }

    public function turnOff(string $gateway, string $feature): void
    {
        $toggleable = $this->resolve($gateway)->gateway();

        if (! $toggleable instanceof Toggleable) {
            throw new \InvalidArgumentException(sprintf(
                'Gateway `%s` is not a toggleable gateway.',
                $gateway
            ));
        }

        $toggleable->turnOff($feature);

        $this->dispatcher->dispatch(new FeatureSwitchedOff($feature, $gateway));
    }

    public function noBlade(): static
    {
        $this->useBlade = false;

        return $this;
    }

    public function noScheduling(): static
    {
        $this->useScheduling = false;

        return $this;
    }

    public function noValidations(): static
    {
        $this->useValidations = false;

        return $this;
    }

    public function noCommands(): static
    {
        $this->useCommands = false;

        return $this;
    }

    public function noMiddlewares(): static
    {
        $this->useMiddlewares = false;

        return $this;
    }

    public function usesBlade(): bool
    {
        return $this->useBlade;
    }

    public function usesValidations(): bool
    {
        return $this->useValidations;
    }

    public function usesScheduling(): bool
    {
        return $this->useScheduling;
    }

    public function usesCommands(): bool
    {
        return $this->useCommands;
    }

    public function usesMiddlewares(): bool
    {
        return $this->useMiddlewares;
    }

    public function extend(string $driver, callable $builder): self
    {
        $this->gatewayDrivers[$driver] = $builder;

        return $this;
    }

    protected function getContainer(): Container
    {
        return $this->container;
    }

    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new \InvalidArgumentException("The [{$name}] feature gateway has not been configured.");
        }

        $gateway = $this->getGateway($config['driver'], $config, $name);
        if (($config['cache'] ?? null) && $gateway instanceof Cacheable) {
            $cache = $this->buildCache($name, $config['cache'], $gateway)
                ->configureTtl($config['cache']['ttl'] ?? 300);
        }
        if (($config['filter'] ?? null)) {
            if (is_string($config['filter']) && Str::contains($config['filter'], '|')) {
                $config['filter'] = explode('|', $config['filter']);
            }
            $config['filter'] = Arr::wrap($config['filter']);
            $filter = new FeatureFilter($config['filter']);
        }

        return new GatewayInspector($gateway, $filter ?? null, $cache ?? null);
    }

    protected function getConfig($name): ?array
    {
        return $this->container->make(Repository::class)->get("features.gateways.{$name}");
    }

    protected function getGateway(string $driver, array $config, string $name): Gateway
    {
        if (! $this->driverIsNative($driver) &&
            ! isset($this->gatewayDrivers[$driver])
        ) {
            throw new \InvalidArgumentException("No gateway for [$driver].");
        }

        if ($this->driverIsNative($driver)) {
            return call_user_func([$this, 'build' . Str::ucfirst(Str::camel($driver)) . 'Gateway'], $config, $name);
        }

        return call_user_func($this->gatewayDrivers[$driver], $config, $name);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function pipes(): array
    {
        $pipes = $this->container->make(Repository::class)->get('features.pipeline');

        return collect($pipes)
            ->map(fn (string $pipe) => $this->resolve($pipe))
            ->all();
    }

    protected function buildCache(string $namespace, array $config, Cacheable $cacheable): GatewayCache
    {
        $cache = $this->getContainer()->make(CacheManager::class)->driver($config['store'] ?? null);

        return (new GatewayCache($cache, $namespace, $cacheable))
            ->configureTtl($config['ttl']);
    }

    protected function buildDatabaseGateway(array $config): DatabaseGateway
    {
        return new DatabaseGateway(
            connection: $this->getContainer()->make(DatabaseManager::class)->connection(
                $config['connection'] ?? null
            ),
            table: $config['table'] ?? 'features',
            field: $config['field'] ?? 'active_at',
        );
    }

    protected function buildRedisGateway(array $config): RedisGateway
    {
        return new RedisGateway(
            connection: $this->getContainer()->make(RedisManager::class)->connection($config['connection'] ?? null),
            prefix: $config['prefix'] ?? 'features',
        );
    }

    protected function buildGateGateway(array $config, string $name): GateGateway
    {
        if (empty($config['gate'])) {
            throw new \RuntimeException(sprintf('No gate is configured for connection `%s`', $name));
        }

        return new GateGateway(
            $this->getContainer()->make(AuthManager::class)->guard($config['guard'] ?? null),
            $this->getContainer()->make(Gate::class),
            $config['gate']
        );
    }

    protected function buildInMemoryGateway(array $config): InMemoryGateway
    {
        return new InMemoryGateway(
            loader: new FileLoader(new FeaturesFileDiscoverer(
                application: $this->getContainer()->make(Application::class),
                file: $config['file'] ?? null,
            ), container: $this->getContainer()),
        );
    }

    protected function driverIsNative(string $driver): bool
    {
        return method_exists($this, 'build' . Str::ucfirst(Str::camel($driver)) . 'Gateway');
    }
}
