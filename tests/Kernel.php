<?php

namespace YlsIdeas\FeatureFlags\Tests;

class Kernel extends \Orchestra\Testbench\Foundation\Http\Kernel
{
    protected $middleware = [
        \YlsIdeas\FeatureFlags\Middlewares\PreventRequestsDuringMaintenance::class,
    ];
}
