<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Before;
use SplFileInfo;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;
use YlsIdeas\FeatureFlags\Manager;

class FeatureFlagsServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FeatureFlagsServiceProvider::class,
        ];
    }

    #[Before]
    protected function cleanUp(): void
    {
        $this->afterApplicationCreated(function (): void {
            File::delete(config_path('features.php'));
            File::delete(base_path('.features.php'));

            File::delete(app_path('Http/Middleware/PreventRequestsDuringMaintenance.php.backup'));

            collect(File::files(database_path('migrations')))
                ->each(fn (SplFileInfo $file) => File::delete($file->getPathname()));
        });
    }

    public function test_adds_manager_to_the_container(): void
    {
        $gateway = $this->app->make(Manager::class);

        $this->assertInstanceOf(Manager::class, $gateway);
    }

    public function test_publishes_the_features_config(): void
    {
        $this->assertFalse(File::exists(config_path('features.php')));

        $this->artisan('vendor:publish', [
            '--tag' => 'config',
            '--force' => true,
        ]);

        $this->assertTrue(File::exists(config_path('features.php')));
    }

    public function test_publishes_the_in_memory_features_config(): void
    {
        $this->assertFalse(File::exists(base_path('.features.php')));

        $this->artisan('vendor:publish', [
            '--tag' => 'inmemory-config',
            '--force' => true,
        ]);

        $this->assertTrue(File::exists(base_path('.features.php')));
    }

    public function test_publishes_the_features_migration(): void
    {
        $this->assertNull(
            collect(File::files(database_path('migrations')))
                ->first(fn (SplFileInfo $file) => Str::endsWith($file->getFilename(), '_create_features_table.php'))
        );

        $this->artisan('vendor:publish', [
            '--tag' => 'features-migration',
            '--force' => true,
        ]);

        $filename =
            collect(File::files(database_path('migrations')))
                ->first(fn (SplFileInfo $file) => Str::endsWith($file->getFilename(), '_create_features_table.php'));

        $this->assertNotNull($filename);
    }

    public function test_publishes_the_maintenance_middleware(): void
    {
        $this->artisan('vendor:publish', [
            '--tag' => 'maintenance-middleware',
            '--force' => true,
        ]);

        $this->assertTrue(File::exists(app_path('Http/Middleware/PreventRequestsDuringMaintenance.php')));

        $this->assertStringContainsString(
            'use YlsIdeas\FeatureFlags\Middlewares\PreventRequestsDuringMaintenance as Middleware;',
            File::get(app_path('Http/Middleware/PreventRequestsDuringMaintenance.php'))
        );
    }

    public function test_posting_about_info(): void
    {
        if (version_compare(Application::VERSION, '9.20.0', '<')) {
            $this->markTestSkipped('Not available before Laravel 9.20.0');
        }

        config()->set('features.pipeline', ['in_memory']);

        $this->artisan('about')
            ->expectsOutputToContain('Feature Flags')
            ->expectsOutputToContain('Pipeline')
            ->run();
    }
}
