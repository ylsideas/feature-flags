<?php

namespace YlsIdeas\FeatureFlags;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use YlsIdeas\FeatureFlags\Commands;
use YlsIdeas\FeatureFlags\Contracts\Gateway;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\Rules\FeatureOnRule;

class FeatureFlagsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/features.php' => config_path('features.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../config/.features.php' => base_path('.features.php'),
            ], 'inmemory-config');

            // Publishing the migrations.
            $migration = date('Y_m_d_His').'_create_features_table.php';
            $this->publishes([
                __DIR__.'/../migrations/create_features_table.php' => database_path('migrations/'.$migration),
            ], 'features-migration');

            // Registering package commands.
            if (Features::usesCommands()) {
                $this->commands([
                    Commands\SwitchOnFeature::class,
                    Commands\SwitchOffFeature::class,
                ]);
            }
        }

        if (Features::usesValidations()) {
            $this->validator();
        }

        if (Features::usesScheduling()) {
            $this->schedulingMacros();
        }

        if (Features::usesBlade()) {
            $this->bladeDirectives();
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/features.php', 'features');

        if (method_exists($this->app, 'scoped')) {
            $this->app->scoped(Gateway::class, Manager::class);
        } else {
            $this->app->singleton(Gateway::class, Manager::class);
        }
    }

    protected function schedulingMacros()
    {
        if (! Event::hasMacro('skipWithoutFeature')) {
            Event::macro('skipWithoutFeature', function ($feature) {
                /** @var Event $this */
                return $this->skip(function () use ($feature) {
                    return ! Features::accessible($feature);
                });
            });
        }

        if (! Event::hasMacro('skipWithFeature')) {
            Event::macro('skipWithFeature', function ($feature) {
                /** @var Event $this */
                return $this->skip(function () use ($feature) {
                    return Features::accessible($feature);
                });
            });
        }
    }

    protected function bladeDirectives()
    {
        Blade::if('feature', function (string $feature, $applyIfOn = true) {
            return $applyIfOn
                ? Features::accessible($feature)
                : ! Features::accessible($feature);
        });
    }

    protected function validator()
    {
        Validator::extendImplicit('requiredWithFeature', FeatureOnRule::class);
    }
}
