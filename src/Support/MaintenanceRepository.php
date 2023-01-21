<?php

namespace YlsIdeas\FeatureFlags\Support;

use Illuminate\Contracts\Container\Container;
use YlsIdeas\FeatureFlags\Contracts\Features;
use YlsIdeas\FeatureFlags\Contracts\Maintenance;

class MaintenanceRepository implements Maintenance
{
    public array $scenarios = [];

    public ?MaintenanceScenario $foundScenario = null;
    protected \Closure $uponActivation;
    protected \Closure $uponDeactivation;

    public function __construct(protected Features $features, protected Container $container)
    {
    }

    public function uponActivation(callable $callable): static
    {
        $this->uponActivation = \Closure::fromCallable($callable);

        return $this;
    }

    public function uponDeactivation(callable $callable): static
    {
        $this->uponDeactivation = \Closure::fromCallable($callable);

        return $this;
    }

    public function callActivation(array $properties): void
    {
        $this->container->call($this->uponActivation, [
            'properties' => $properties, 'features' => $this->features
        ]);
    }

    public function callDeactivation(): void
    {
        $this->container->call($this->uponDeactivation, ['features' => $this->features]);
    }

    public function onEnabled($feature): MaintenanceScenario
    {
        return tap((new MaintenanceScenario())->whenEnabled($feature), function (MaintenanceScenario $scenario) {
            $this->scenarios[] = $scenario;
        });
    }

    public function onDisabled($feature): MaintenanceScenario
    {
        return tap((new MaintenanceScenario())->whenDisabled($feature), function (MaintenanceScenario $scenario) {
            $this->scenarios[] = $scenario;
        });
    }

    public function active(): bool
    {
        return (bool) $this->findScenario();
    }

    public function parameters(): ?array
    {
        return $this->foundScenario?->toArray();
    }

    protected function findScenario(): ?MaintenanceScenario
    {
        return $this->foundScenario = collect($this->scenarios)
            ->first(function (MaintenanceScenario $scenario) {
                if ($scenario->onEnabled && $this->features->accessible($scenario->feature)) {
                    return true;
                }
                if (! $scenario->onEnabled && ! $this->features->accessible($scenario->feature)) {
                    return true;
                }

                return false;
            });
    }
}
