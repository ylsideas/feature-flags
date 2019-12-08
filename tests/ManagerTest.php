<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Events\Dispatcher;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Events\FeatureAccessed;
use YlsIdeas\FeatureFlags\Events\FeatureAccessing;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOff;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOn;
use YlsIdeas\FeatureFlags\Manager;
use YlsIdeas\FeatureFlags\Repositories\ChainRepository;
use YlsIdeas\FeatureFlags\Repositories\DatabaseRepository;
use YlsIdeas\FeatureFlags\Repositories\InMemoryRepository;
use YlsIdeas\FeatureFlags\Repositories\RedisRepository;

/**
 * @covers \YlsIdeas\FeatureFlags\Manager
 */
class ManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $app;

    public function setUp(): void
    {
        parent::setUp();
        $this->app = \Mockery::mock(Application::class);

        // Required For Laravel 6 but not 5.8
        $config = \Mockery::mock(\Illuminate\Contracts\Config\Repository::class);
        $this->app->shouldReceive('make')
            ->with('config')
            ->andReturn($config);
    }

    /** @test */
    public function itCanBeInitialised()
    {
        $manager = new Manager($this->app, \Mockery::mock(Dispatcher::class));

        $this->assertInstanceOf(Manager::class, $manager);
    }

    /** @test */
    public function itCanProvidesDefaultDriver()
    {
        $config = \Mockery::mock(Repository::class);

        $this->app->shouldReceive('make')
            ->with(\Illuminate\Contracts\Config\Repository::class)
            ->andReturn($config)
            ->once();

        $config->shouldReceive('get')
            ->with('features.default')
            ->andReturn('database');

        $manager = new Manager($this->app, \Mockery::mock(Dispatcher::class));

        $this->assertEquals('database', $manager->getDefaultDriver());
    }

    /** @test */
    public function itCanCheckIfFeaturesAreAccessible()
    {
        $dispatcher = \Mockery::mock(Dispatcher::class);
        $config = \Mockery::mock(Repository::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);

        $this->app->shouldReceive('make')
            ->with(\Illuminate\Contracts\Config\Repository::class)
            ->andReturn($config)
            ->once();

        $this->app->shouldReceive('make')
            ->with(DatabaseRepository::class)
            ->once()
            ->andReturn($databaseRepository);

        $config->shouldReceive('get')
            ->with('features.default')
            ->andReturn('database');

        $dispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(FeatureAccessing::class))
            ->once();

        $dispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(FeatureAccessed::class))
            ->once();

        $databaseRepository->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(true);

        $manager = new Manager($this->app, $dispatcher);

        $this->assertTrue($manager->accessible('my-feature'));
    }

    /** @test */
    public function itCanFetchAllTheFeaturesAndTheirCurrentState()
    {
        $dispatcher = \Mockery::mock(Dispatcher::class);
        $config = \Mockery::mock(Repository::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);

        $this->app->shouldReceive('make')
            ->with(\Illuminate\Contracts\Config\Repository::class)
            ->andReturn($config)
            ->once();

        $this->app->shouldReceive('make')
            ->with(DatabaseRepository::class)
            ->once()
            ->andReturn($databaseRepository);

        $config->shouldReceive('get')
            ->with('features.default')
            ->andReturn('database');

        $databaseRepository->shouldReceive('all')
            ->once()
            ->andReturn([
                'my-feature' => true,
            ]);

        $manager = new Manager($this->app, $dispatcher);

        $this->assertSame(['my-feature' => true], $manager->all());
    }

    /** @test */
    public function itCanTurnOnFeatures()
    {
        $dispatcher = \Mockery::mock(Dispatcher::class);
        $config = \Mockery::mock(Repository::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);

        $this->app->shouldReceive('make')
            ->with(\Illuminate\Contracts\Config\Repository::class)
            ->andReturn($config)
            ->once();

        $this->app->shouldReceive('make')
            ->with(DatabaseRepository::class)
            ->once()
            ->andReturn($databaseRepository);

        $config->shouldReceive('get')
            ->with('features.default')
            ->andReturn('database');

        $dispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(FeatureSwitchedOn::class))
            ->once();

        $databaseRepository->shouldReceive('turnOn')
            ->with('my-feature')
            ->once();

        $manager = new Manager($this->app, $dispatcher);

        $manager->turnOn('my-feature');
    }

    /** @test */
    public function itCanTurnOffFeatures()
    {
        $dispatcher = \Mockery::mock(Dispatcher::class);
        $config = \Mockery::mock(Repository::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);

        $this->app->shouldReceive('make')
            ->with(\Illuminate\Contracts\Config\Repository::class)
            ->andReturn($config)
            ->once();

        $this->app->shouldReceive('make')
            ->with(DatabaseRepository::class)
            ->once()
            ->andReturn($databaseRepository);

        $config->shouldReceive('get')
            ->with('features.default')
            ->andReturn('database');

        $dispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(FeatureSwitchedOff::class))
            ->once();

        $databaseRepository->shouldReceive('turnOff')
            ->with('my-feature')
            ->once();

        $manager = new Manager($this->app, $dispatcher);

        $manager->turnOff('my-feature');
    }

    /**
     * @test
     * @dataProvider drivers
     */
    public function itCanCreateDrivers($driver, $repository)
    {
        $instance = \Mockery::mock($repository);

        $this->app->shouldReceive('make')
            ->with($repository)
            ->once()
            ->andReturn($instance);

        $manager = new Manager($this->app, \Mockery::mock(Dispatcher::class));

        $this->assertSame($instance, $manager->driver($driver));
    }

    /**
     * @test
     * @dataProvider services
     */
    public function isCanFlagPartsOfThePackageToBeTurnedOff($item)
    {
        $manager = new Manager($this->app, \Mockery::mock(Dispatcher::class));

        $this->assertTrue($manager->{"uses$item"}());

        $manager->{"no$item"}();

        $this->assertFalse($manager->{"uses$item"}());
    }

    public function services()
    {
        return [
            ['Blade'],
            ['Validations'],
            ['Commands'],
            ['Scheduling'],
        ];
    }

    public function drivers()
    {
        return [
            ['database', DatabaseRepository::class],
            ['config', InMemoryRepository::class],
            ['chain', ChainRepository::class],
            ['redis', RedisRepository::class],
        ];
    }
}
