<?php

namespace YlsIdeas\FeatureFlags\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use YlsIdeas\FeatureFlags\Contracts\Repository;
use YlsIdeas\FeatureFlags\Support\StateChecking;

class FeatureFlagState
{
    use StateChecking;

    /**
     * @var Repository
     */
    protected $repository;
    /**
     * @var Application
     */
    protected $application;

    public function __construct(Repository $repository, Application $application)
    {
        $this->repository = $repository;
        $this->application = $application;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $feature
     * @param string $state
     * @param int $abort
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $feature, string $state = 'on', $abort = 403)
    {
        if (
            ! ($this->check($state)
            ? $this->repository->accessible($feature)
            : ! $this->repository->accessible($feature))
        ) {
            $this->application->abort($abort);
        }

        return $next($request);
    }
}
