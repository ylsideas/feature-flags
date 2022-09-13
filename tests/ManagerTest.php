<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Events\Dispatcher;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\ActionableFlag;
use YlsIdeas\FeatureFlags\Contracts\Gateway;
use YlsIdeas\FeatureFlags\Contracts\Toggleable;
use YlsIdeas\FeatureFlags\Events\FeatureAccessed;
use YlsIdeas\FeatureFlags\Events\FeatureAccessing;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOff;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOn;
use YlsIdeas\FeatureFlags\Manager;
use YlsIdeas\FeatureFlags\Gateways\DatabaseGateway;
use YlsIdeas\FeatureFlags\Gateways\InMemoryGateway;
use YlsIdeas\FeatureFlags\Gateways\RedisGateway;

/**
 * @covers \YlsIdeas\FeatureFlags\Manager
 */
class ManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = \Mockery::mock(Container::class);
    }

    /** @test */
    public function itCanBeInitialised()
    {
        $manager = new Manager($this->container, \Mockery::mock(Dispatcher::class));

        $this->assertInstanceOf(Manager::class, $manager);
    }

    /** @test */
    public function itCanCheckIfFeaturesAreAccessible()
    {
        $dispatcher = \Mockery::mock(Dispatcher::class);
        $config = \Mockery::mock(Repository::class);

        $this->container->shouldReceive('make')
            ->with(\Illuminate\Contracts\Config\Repository::class)
            ->andReturn($config)
            ->twice();

        $config->shouldReceive('get')
            ->with('features.pipeline')
            ->andReturn(['test']);

        $config->shouldReceive('get')
            ->with('features.gateways.test')
            ->andReturn([
                'driver' => 'null'
            ]);

        $dispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(FeatureAccessing::class))
            ->once();

        $dispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(FeatureAccessed::class))
            ->once();

        $manager = new Manager($this->container, $dispatcher);
        $manager->extend('null', function () {
            return \Mockery::mock(Gateway::class)
                ->shouldReceive('accessible')
                ->with('my-feature')
                ->andReturn(true)
                ->getMock();
        });

        $this->assertTrue($manager->accessible('my-feature'));
    }

    /** @test */
    public function itCanTurnOnFeatures()
    {
        $dispatcher = \Mockery::mock(Dispatcher::class);
        $config = \Mockery::mock(Repository::class);

        $this->container->shouldReceive('make')
            ->with(\Illuminate\Contracts\Config\Repository::class)
            ->andReturn($config)
            ->once();

        $config->shouldReceive('get')
            ->with('features.pipeline')
            ->andReturn(['test']);

        $config->shouldReceive('get')
            ->with('features.gateways.test')
            ->andReturn([
                'driver' => 'null'
            ]);

        $dispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(FeatureSwitchedOn::class))
            ->once();

        $manager = new Manager($this->container, $dispatcher);
        $manager->extend('null', function () {
            return \Mockery::mock(Gateway::class, Toggleable::class)
                ->shouldReceive('turnOn')
                ->with('my-feature')
                ->getMock();
        });

        $manager->turnOn('test', 'my-feature');
    }

    /** @test */
    public function itCanTurnOffFeatures()
    {
        $dispatcher = \Mockery::mock(Dispatcher::class);
        $config = \Mockery::mock(Repository::class);

        $this->container->shouldReceive('make')
            ->with(\Illuminate\Contracts\Config\Repository::class)
            ->andReturn($config)
            ->once();

        $config->shouldReceive('get')
            ->with('features.pipeline')
            ->andReturn(['test']);

        $config->shouldReceive('get')
            ->with('features.gateways.test')
            ->andReturn([
                'driver' => 'null'
            ]);

        $dispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(FeatureSwitchedOff::class))
            ->once();

        $manager = new Manager($this->container, $dispatcher);
        $manager->extend('null', function () {
            return \Mockery::mock(Gateway::class, Toggleable::class)
                ->shouldReceive('turnOff')
                ->with('my-feature')
                ->getMock();
        });

        $manager->turnOff('test', 'my-feature');
    }

//    /**
//     * @test
//     * @dataProvider drivers
//     */
//    public function itCanCreateDrivers($driver, $repository)
//    {
//        $instance = \Mockery::mock($repository);
//
//        $this->container->shouldReceive('make')
//            ->with($repository)
//            ->once()
//            ->andReturn($instance);
//
//        $manager = new Manager($this->container, \Mockery::mock(Dispatcher::class));
//
//        $this->assertSame($instance, $manager->driver($driver));
//    }

    /**
     * @test
     * @dataProvider services
     */
    public function itCanFlagPartsOfThePackageToBeTurnedOff($item)
    {
        $manager = new Manager($this->container, \Mockery::mock(Dispatcher::class));

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
}
