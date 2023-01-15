<?php

namespace YlsIdeas\FeatureFlags;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Foundation\MaintenanceModeManager;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use YlsIdeas\FeatureFlags\Contracts\Features as FeaturesContract;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\Middlewares\GuardFeature;
use YlsIdeas\FeatureFlags\Rules\FeatureOnRule;
use YlsIdeas\FeatureFlags\Support\MaintenanceDriver;
use YlsIdeas\FeatureFlags\Support\MaintenanceRepository;
use YlsIdeas\FeatureFlags\Support\QueryBuilderMixin;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\FeatureFlagsServiceProviderTest
 */
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

            $this->aboutCommandInfo();
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

        if (Features::usesMiddlewares()) {
            $this->app->make(Router::class)
                ->aliasMiddleware('feature', GuardFeature::class);
        }

        if (Features::usesQueryBuilderMixin()) {
            $this->queryBuilder();
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/features.php', 'features');

        if (method_exists($this->app, 'scoped')) {
            $this->app->scoped(FeaturesContract::class, Manager::class);
        } else {
            $this->app->singleton(FeaturesContract::class, Manager::class);
        }

        $this->app->scoped(MaintenanceRepository::class, function (Container $app) {
            return new MaintenanceRepository($app->make(Manager::class), $app);
        });

        $this->app->extend(MaintenanceModeManager::class, function (MaintenanceModeManager $manager) {
            return $manager->extend('features', function (): MaintenanceMode {
                return new MaintenanceDriver(
                    $this->app->make(MaintenanceRepository::class)
                );
            });
        });
    }

    protected function schedulingMacros()
    {
        if (! Event::hasMacro('skipWithoutFeature')) {
            /** @noRector \Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector */
            Event::macro('skipWithoutFeature', function (string $feature): Event {
                /** @var Event $this */
                return $this->skip(fn () => ! Features::accessible($feature));
            });
        }

        if (! Event::hasMacro('skipWithFeature')) {
            /** @noRector \Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector */
            Event::macro('skipWithFeature', function ($feature): Event {
                /** @var Event $this */
                return $this->skip(fn () => Features::accessible($feature));
            });
        }
    }

    protected function bladeDirectives()
    {
        Blade::if('feature', fn (string $feature, $applyIfOn = true) => $applyIfOn
            ? Features::accessible($feature)
            : ! Features::accessible($feature));
    }

    protected function validator()
    {
        Validator::extendImplicit('requiredWithFeature', FeatureOnRule::class);
    }

    protected function queryBuilder()
    {
        Builder::mixin(new QueryBuilderMixin());
    }

    protected function aboutCommandInfo(): void
    {
        if (class_exists(AboutCommand::class)) {
            AboutCommand::add('Feature Flags', [
                'Pipeline' => fn () => implode(', Hello', config('features.pipeline')),
            ]);
        }
    }
}
