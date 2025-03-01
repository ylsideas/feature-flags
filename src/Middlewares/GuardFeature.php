<?php

namespace YlsIdeas\FeatureFlags\Middlewares;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use YlsIdeas\FeatureFlags\Contracts\Features;
use YlsIdeas\FeatureFlags\Support\StateChecking;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Middlewares\GuardFeatureTest
 */
class GuardFeature
{
    use StateChecking;

    public function __construct(
        protected Features $manager,
        protected Application $application,
        protected Translator $translator,
    ) {
    }

    /**
     * @throws BindingResolutionException
     */
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
            $this->application->abort($abort, $this->translator->get($message));
        }

        return $next($request);
    }
}
