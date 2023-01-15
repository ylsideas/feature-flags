<?php

namespace YlsIdeas\FeatureFlags\Support;

use Illuminate\Contracts\Foundation\MaintenanceMode;
use YlsIdeas\FeatureFlags\Contracts\Maintenance as MaintenanceContract;

class MaintenanceDriver implements MaintenanceMode
{
    public function __construct(protected MaintenanceContract $features)
    {

    }

    public function activate(array $payload): void
    {
        $this->features->callActivation($payload);
    }

    public function deactivate(): void
    {
        $this->features->callDeactivation();
    }

    public function active(): bool
    {
        return $this->features->active();
    }

    public function data(): array
    {
        return $this->features->parameters() ?? [];
    }
}
