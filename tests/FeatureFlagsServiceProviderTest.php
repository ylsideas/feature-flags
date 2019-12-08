<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;
use YlsIdeas\FeatureFlags\Contracts\Repository;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;
use YlsIdeas\FeatureFlags\Manager;
use YlsIdeas\FeatureFlags\Repositories\ChainRepository;
use YlsIdeas\FeatureFlags\Repositories\DatabaseRepository;
use YlsIdeas\FeatureFlags\Repositories\InMemoryRepository;
use YlsIdeas\FeatureFlags\Repositories\RedisRepository;

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
    public function addsTheDatabaseRepositoryToTheContainer()
    {
        $repository = $this->app->make(DatabaseRepository::class);

        $this->assertInstanceOf(Repository::class, $repository);
        $this->assertInstanceOf(DatabaseRepository::class, $repository);
    }

    /** @test */
    public function addsTheInMemoryRepositoryToTheContainer()
    {
        $repository = $this->app->make(InMemoryRepository::class);

        $this->assertInstanceOf(Repository::class, $repository);
        $this->assertInstanceOf(InMemoryRepository::class, $repository);
    }

    /** @test */
    public function addsTheChainRepositoryToTheContainer()
    {
        $repository = $this->app->make(ChainRepository::class);

        $this->assertInstanceOf(Repository::class, $repository);
        $this->assertInstanceOf(ChainRepository::class, $repository);
    }

    /** @test */
    public function addsTheRedisRepositoryToTheContainer()
    {
        $this->app->extend(RedisManager::class, function () {
            $connection = $this->mock(Connection::class);
            $redis = $this->mock(RedisManager::class);

            $redis->shouldReceive('connection')
                ->with('default')
                ->once()
                ->andReturn($connection);

            return $redis;
        });

        $repository = $this->app->make(RedisRepository::class);

        $this->assertInstanceOf(Repository::class, $repository);
        $this->assertInstanceOf(RedisRepository::class, $repository);
    }

    /** @test */
    public function addsManagerToTheContainer()
    {
        $repository = $this->app->make(Manager::class);

        $this->assertInstanceOf(Manager::class, $repository);
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
