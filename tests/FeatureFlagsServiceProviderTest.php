<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;
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

    public function setUp(): void
    {
        parent::setUp();
        $this->cleanUp();
    }

    public function tearDown(): void
    {
        $this->cleanUp();
        parent::tearDown();
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
                ->first(fn (\SplFileInfo $file) => Str::endsWith($file->getFilename(), '_create_features_table.php'))
        );

        $this->artisan('vendor:publish', [
            '--tag' => 'features-migration',
            '--force' => true,
        ]);

        $filename =
            collect(File::files(database_path('migrations')))
                ->first(fn (\SplFileInfo $file) => Str::endsWith($file->getFilename(), '_create_features_table.php'));

        $this->assertNotNull($filename);
    }

    public function test_posting_about_info(): void
    {
        config()->set('features.pipeline', ['in_memory']);

        $this->artisan('about')
            ->expectsOutputToContain('Feature Flags')
            ->expectsOutputToContain('Pipeline')
            ->run();
    }

    protected function cleanUp(): void
    {
        File::delete(config_path('features.php'));
        File::delete(base_path('.features.php'));

        collect(File::files(database_path('migrations')))
            ->each(fn (\SplFileInfo $file) => File::delete($file->getPathname()));
    }
}
