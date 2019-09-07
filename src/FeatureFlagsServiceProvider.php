<?php

namespace YlsIdeas\FeatureFlags;

use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Validator;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\Rules\FeatureOnRule;
use YlsIdeas\FeatureFlags\Contracts\Repository;
use Illuminate\Contracts\Foundation\Application;
use YlsIdeas\FeatureFlags\Commands\SwitchOnFeature;
use YlsIdeas\FeatureFlags\Commands\SwitchOffFeature;
use YlsIdeas\FeatureFlags\Repositories\ChainRepository;
use YlsIdeas\FeatureFlags\Repositories\RedisRepository;
use YlsIdeas\FeatureFlags\Repositories\DatabaseRepository;
use YlsIdeas\FeatureFlags\Repositories\InMemoryRepository;

class FeatureFlagsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('features.php'),
            ], 'config');

            // Publishing the migrations.
            $migration = date('Y_m_d_His').'_create_features_table.php';
            $this->publishes([
                __DIR__.'/../migrations/create_features_table.php' => database_path('migrations/'.$migration),
            ], 'features-migration');

            // Registering package commands.
            if (Features::usesCommands()) {
                $this->commands([
                    SwitchOnFeature::class,
                    SwitchOffFeature::class,
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
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'features');

        $this->app->singleton(Repository::class, Manager::class);

        $this->app->singleton(InMemoryRepository::class, function () {
            return new InMemoryRepository(config(config('features.repositories.config.key')));
        });

        $this->app->singleton(RedisRepository::class, function () {
            return new RedisRepository(
                $this->app->make(RedisManager::class)
                    ->connection(config('features.repositories.redis.connection')),
                config('features.repositories.redis.prefix')
            );
        });

        $this->app->singleton(DatabaseRepository::class, function () {
            return new DatabaseRepository(
                $this->app->make(DatabaseManager::class)
                    ->connection(config('features.repositories.database.connection')),
                config('features.repositories.database.table')
            );
        });

        $this->app->singleton(ChainRepository::class, function () {
            return new ChainRepository(
                $this->app->make(Manager::class),
                config('features.repositories.chain.drivers'),
                config('features.repositories.chain.store')
            );
        });
    }

    protected function schedulingMacros()
    {
        if (! Event::hasMacro('skipWithoutFeature')) {
            Event::macro('skipWithoutFeature', function ($feature) {
                return $this->/* @scrutinizer ignore-call */ skip(function () use ($feature) {
                    return ! Features::accessible($feature);
                });
            });
        }

        if (! Event::hasMacro('skipWithFeature')) {
            Event::macro('skipWithFeature', function ($feature) {
                return $this->/* @scrutinizer ignore-call */ skip(function () use ($feature) {
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
