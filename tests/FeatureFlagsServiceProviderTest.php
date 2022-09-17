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

    public function testAddsManagerToTheContainer()
    {
        $gateway = $this->app->make(Manager::class);

        $this->assertInstanceOf(Manager::class, $gateway);
    }

    public function testPublishesTheFeaturesConfig()
    {
        $this->assertFalse(File::exists(config_path('features.php')));

        $this->artisan('vendor:publish', [
            '--tag' => 'config',
            '--force' => true,
        ]);

        $this->assertTrue(File::exists(config_path('features.php')));
    }

    public function testPublishesTheInMemoryFeaturesConfig()
    {
        $this->assertFalse(File::exists(base_path('.features.php')));

        $this->artisan('vendor:publish', [
            '--tag' => 'inmemory-config',
            '--force' => true,
        ]);

        $this->assertTrue(File::exists(base_path('.features.php')));
    }

    public function testPublishesTheFeaturesMigration()
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

    protected function cleanUp()
    {
        File::delete(config_path('features.php'));
        File::delete(base_path('.features.php'));

        collect(File::files(database_path('migrations')))
            ->each(fn (\SplFileInfo $file) => File::delete($file->getPathname()));
    }
}
