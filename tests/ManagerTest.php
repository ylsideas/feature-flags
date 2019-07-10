<?php

namespace YlsIdeas\FeatureFlags\Tests;

use PHPUnit\Framework\TestCase;
use Illuminate\Events\Dispatcher;
use YlsIdeas\FeatureFlags\Manager;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Foundation\Application;
use YlsIdeas\FeatureFlags\Events\FeatureAccessed;
use YlsIdeas\FeatureFlags\Events\FeatureAccessing;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOn;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOff;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use YlsIdeas\FeatureFlags\Repositories\ChainRepository;
use YlsIdeas\FeatureFlags\Repositories\RedisRepository;
use YlsIdeas\FeatureFlags\Repositories\DatabaseRepository;
use YlsIdeas\FeatureFlags\Repositories\InMemoryRepository;

/**
 * @covers \YlsIdeas\FeatureFlags\Manager
 */
class ManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @test */
    public function itCanBeInitialised()
    {
        $app = \Mockery::mock(Application::class);

        $manager = new Manager($app, \Mockery::mock(Dispatcher::class));

        $this->assertInstanceOf(Manager::class, $manager);
    }

    /** @test */
    public function itCanProvidesDefaultDriver()
    {
        $app = \Mockery::mock(Application::class);
        $config = \Mockery::mock(Repository::class);

        $app->shouldReceive('make')
            ->with(\Illuminate\Contracts\Config\Repository::class)
            ->andReturn($config)
            ->once();

        $config->shouldReceive('get')
            ->with('features.default')
            ->andReturn('database');

        $manager = new Manager($app, \Mockery::mock(Dispatcher::class));

        $this->assertEquals('database', $manager->getDefaultDriver());
    }

    /** @test */
    public function itCanCheckIfFeaturesAreAccessible()
    {
        $app = \Mockery::mock(Application::class);
        $dispatcher = \Mockery::mock(Dispatcher::class);
        $config = \Mockery::mock(Repository::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);

        $app->shouldReceive('make')
            ->with(\Illuminate\Contracts\Config\Repository::class)
            ->andReturn($config)
            ->once();

        $app->shouldReceive('make')
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

        $manager = new Manager($app, $dispatcher);

        $this->assertTrue($manager->accessible('my-feature'));
    }

    /** @test */
    public function itCanFetchAllTheFeaturesAndTheirCurrentState()
    {
        $app = \Mockery::mock(Application::class);
        $dispatcher = \Mockery::mock(Dispatcher::class);
        $config = \Mockery::mock(Repository::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);

        $app->shouldReceive('make')
            ->with(\Illuminate\Contracts\Config\Repository::class)
            ->andReturn($config)
            ->once();

        $app->shouldReceive('make')
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

        $manager = new Manager($app, $dispatcher);

        $this->assertSame(['my-feature' => true], $manager->all());
    }

    /** @test */
    public function itCanTurnOnFeatures()
    {
        $app = \Mockery::mock(Application::class);
        $dispatcher = \Mockery::mock(Dispatcher::class);
        $config = \Mockery::mock(Repository::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);

        $app->shouldReceive('make')
            ->with(\Illuminate\Contracts\Config\Repository::class)
            ->andReturn($config)
            ->once();

        $app->shouldReceive('make')
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

        $manager = new Manager($app, $dispatcher);

        $manager->turnOn('my-feature');
    }

    /** @test */
    public function itCanTurnOffFeatures()
    {
        $app = \Mockery::mock(Application::class);
        $dispatcher = \Mockery::mock(Dispatcher::class);
        $config = \Mockery::mock(Repository::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);

        $app->shouldReceive('make')
            ->with(\Illuminate\Contracts\Config\Repository::class)
            ->andReturn($config)
            ->once();

        $app->shouldReceive('make')
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

        $manager = new Manager($app, $dispatcher);

        $manager->turnOff('my-feature');
    }

    /**
     * @test
     * @dataProvider drivers
     */
    public function itCanCreateDrivers($driver, $repository)
    {
        $app = \Mockery::mock(Application::class);
        $instance = \Mockery::mock($repository);

        $app->shouldReceive('make')
            ->with($repository)
            ->once()
            ->andReturn($instance);

        $manager = new Manager($app, \Mockery::mock(Dispatcher::class));

        $this->assertSame($instance, $manager->driver($driver));
    }

    /**
     * @test
     * @dataProvider services
     */
    public function isCanFlagPartsOfThePackageToBeTurnedOff($item)
    {
        $app = \Mockery::mock(Application::class);
        $manager = new Manager($app, \Mockery::mock(Dispatcher::class));

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
