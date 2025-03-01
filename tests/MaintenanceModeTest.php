<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Generator;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

use function Orchestra\Testbench\after_resolving;

use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;
use YlsIdeas\FeatureFlags\Middlewares\PreventRequestsDuringMaintenance;

class MaintenanceModeTest extends TestCase
{
    public function test_maintenance_mode_enabled(): void
    {
        Features::fake(['system.down' => true]);
        Features::maintenanceMode()
            ->onEnabled('system.down');

        Route::get('/', fn (): string => 'Foo Bar');

        $this->get('/')
            ->assertServiceUnavailable();

        Features::assertAccessed('system.down');
    }

    public function test_maintenance_mode_disabled(): void
    {
        Features::fake(['system.down' => false]);
        Features::maintenanceMode()
            ->onEnabled('system.down');

        Route::get('/', fn (): string => 'Foo Bar');

        $this->get('/')
            ->assertOk();

        Features::assertAccessed('system.down');
    }

    public function test_it_handles_the_first_match(): void
    {
        Features::fake(['system.api' => true,]);
        Features::maintenanceMode()
            ->onEnabled('system.down');

        Features::maintenanceMode()
            ->onEnabled('system.api')
            ->statusCode(500);

        Route::get('/', fn (): string => 'Foo Bar');

        $this->get('/')
            ->assertStatus(500);

        Features::assertAccessed('system.down');
        Features::assertAccessed('system.api');
    }

    public function test_upon_activation(): void
    {
        $called = false;
        Features::maintenanceMode()
            ->uponActivation(function () use (&$called): void {
                $called = true;
            });

        $this->artisan('down');

        $this->assertTrue($called);
    }

    #[DataProvider('exceptsValues')]
    public function test_maintenance_mode_respects_excepts_values(string $path, int $status): void
    {
        Features::fake(['system.down' => true]);
        Features::maintenanceMode()
            ->onEnabled('system.down')
            ->exceptPaths(['/test']);

        Route::get('/', fn (): string => 'Foo Bar');
        Route::get('/test', fn (): string => 'Foo Bar Foo');

        $this
            ->withoutExceptionHandling([HttpException::class])
            ->get($path)
            ->assertStatus($status);

        Features::assertAccessed('system.down');
    }

    public static function exceptsValues(): Generator
    {
        yield 'blocked' => [
            '/', 503,
        ];
        yield 'allowed' => [
            '/test', 200,
        ];
    }

    public function test_upon_deactivation(): void
    {
        $called = false;
        Features::fake(['system.down' => true]);

        Features::maintenanceMode()
            ->uponDeactivation(function () use (&$called): void {
                $called = true;
            })
            ->onEnabled('system.down');

        $this->artisan('up');

        $this->assertTrue($called);
    }

    protected function defineEnvironment($app): void
    {
        config()->set('app.maintenance.driver', 'features');
        config()->set('features.pipeline', []);
    }

    /**
     * Required override for Pre Laravel 11
     *
     * @param Application $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        // Required to establish the package's PreventRequestsDuringMaintenance middleware
        // over the default installed by Laravel
        $app->singleton(
            Kernel::class,
            \YlsIdeas\FeatureFlags\Tests\Kernel::class
        );
    }

    /**
     * Required override for Laravel 11
     *
     * @param Application $app
     * @return void
     */
    protected function resolveApplicationHttpMiddlewares($app)
    {
        after_resolving($app, Kernel::class, function ($kernel, $app): void {
            /** @var \Illuminate\Foundation\Http\Kernel $kernel */
            $middleware = new Middleware();

            $kernel->setGlobalMiddleware([
                PreventRequestsDuringMaintenance::class,
            ]);
            $kernel->setMiddlewareGroups($middleware->getMiddlewareGroups());
            $kernel->setMiddlewareAliases($middleware->getMiddlewareAliases());
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            FeatureFlagsServiceProvider::class,
        ];
    }
}
