<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;

class MaintenanceModeTest extends TestCase
{
    public function test_maintenance_mode_enabled()
    {
        Features::fake(['system.down' => true]);
        Features::maintenanceMode()
            ->onEnabled('system.down');

        Route::get('/', fn () => 'Foo Bar');

        $this->get('/')
            ->assertServiceUnavailable();

        Features::assertAccessed('system.down');
    }

    public function test_maintenance_mode_disabled()
    {
        Features::fake(['system.down' => false]);
        Features::maintenanceMode()
            ->onEnabled('system.down');

        Route::get('/', fn () => 'Foo Bar');

        $this->get('/')
            ->assertOk();

        Features::assertAccessed('system.down');
    }

    public function test_it_handles_the_first_match()
    {
        Features::fake(['system.api' => true,]);
        Features::maintenanceMode()
            ->onEnabled('system.down');

        Features::maintenanceMode()
            ->onEnabled('system.api')
            ->statusCode(500);

        Route::get('/', fn () => 'Foo Bar');

        $this->get('/')
            ->assertStatus(500);

        Features::assertAccessed('system.down');
        Features::assertAccessed('system.api');
    }

    public function test_upon_activation()
    {
        $called = false;
        Features::maintenanceMode()
            ->uponActivation(function () use (&$called) {
                $called = true;
            });

        $this->artisan('down');

        $this->assertTrue($called);
    }

    /**
     * @dataProvider exceptsValues
     */
    public function test_maintenance_mode_respects_excepts_values(string $path, int $status)
    {
        Features::fake(['system.down' => true]);
        Features::maintenanceMode()
            ->onEnabled('system.down')
            ->exceptPaths(['/test']);

        Route::get('/', fn () => 'Foo Bar');
        Route::get('/test', fn () => 'Foo Bar Foo');

        $this
            ->withoutExceptionHandling([\Symfony\Component\HttpKernel\Exception\HttpException::class])
            ->get($path)
            ->assertStatus($status);

        Features::assertAccessed('system.down');
    }

    public static function exceptsValues(): \Generator
    {
        yield 'blocked' => [
            '/', 503,
        ];
        yield 'allowed' => [
            '/test', 200,
        ];
    }

    public function test_upon_deactivation()
    {
        $called = false;
        Features::fake(['system.down' => true]);

        Features::maintenanceMode()
            ->uponDeactivation(function () use (&$called) {
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
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
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

    protected function getPackageProviders($app): array
    {
        return [
            FeatureFlagsServiceProvider::class,
        ];
    }
}
