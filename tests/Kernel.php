<?php

namespace YlsIdeas\FeatureFlags\Tests;

use YlsIdeas\FeatureFlags\Middlewares\PreventRequestsDuringMaintenance;

class Kernel extends \Orchestra\Testbench\Foundation\Http\Kernel
{
    protected $middleware = [
        PreventRequestsDuringMaintenance::class,
    ];
}
