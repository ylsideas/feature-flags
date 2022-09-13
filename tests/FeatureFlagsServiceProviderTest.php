<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;
use YlsIdeas\FeatureFlags\Manager;

class FeatureFlagsServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
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

    /** @test */
    public function addsManagerToTheContainer()
    {
        $gateway = $this->app->make(Manager::class);

        $this->assertInstanceOf(Manager::class, $gateway);
    }

    /** @test */
    public function publishesTheFeaturesConfig()
    {
        $this->assertFalse(File::exists(config_path('features.php')));

        $this->artisan('vendor:publish', [
            '--tag' => 'config',
            '--force' => true,
        ]);

        $this->assertTrue(File::exists(config_path('features.php')));
    }

    /** @test */
    public function publishesTheFeaturesMigration()
    {
        $this->assertNull(
            collect(File::files(database_path('migrations')))
                ->first(function (\SplFileInfo $file) {
                    return Str::endsWith($file->getFilename(), '_create_features_table.php');
                })
        );

        $this->artisan('vendor:publish', [
            '--tag' => 'features-migration',
            '--force' => true,
        ]);

        $filename =
            collect(File::files(database_path('migrations')))
                ->first(function (\SplFileInfo $file) {
                    return Str::endsWith($file->getFilename(), '_create_features_table.php');
                });

        $this->assertNotNull($filename);
    }

    protected function cleanUp()
    {
        File::delete(config_path('features.php'));

        collect(File::files(database_path('migrations')))
            ->each(function (\SplFileInfo $file) {
                return File::delete($file->getPathname());
            });
    }
}
