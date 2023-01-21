<?php

namespace YlsIdeas\FeatureFlags\Middlewares;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as BasePreventRequestsDuringMaintenance;

class PreventRequestsDuringMaintenance extends BasePreventRequestsDuringMaintenance
{
    public function getExcludedPaths()
    {
        return $this->app->maintenanceMode()->data()['except'] ?? [];
    }
}
