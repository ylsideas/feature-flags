<?php

namespace YlsIdeas\FeatureFlags\Middlewares;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use YlsIdeas\FeatureFlags\Manager;
use YlsIdeas\FeatureFlags\Support\StateChecking;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Middlewares\GuardFeatureTest
 */
class GuardFeature
{
    use StateChecking;

    public function __construct(protected Manager $manager, protected Application $application)
    {
    }

    public function handle(
        Request $request,
        Closure $next,
        string $feature,
        string $state = 'on',
        $abort = 403,
        $message = '',
    ): mixed {
        if (
            ($this->check($state)
                ? ! $this->manager->accessible($feature)
                : $this->manager->accessible($feature))
        ) {
            $this->application->abort($abort, $message);
        }

        return $next($request);
    }
}
