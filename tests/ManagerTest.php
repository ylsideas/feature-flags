<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Events\Dispatcher;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Contracts\Gateway;
use YlsIdeas\FeatureFlags\Contracts\Toggleable;
use YlsIdeas\FeatureFlags\Events\FeatureAccessed;
use YlsIdeas\FeatureFlags\Events\FeatureAccessing;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOff;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOn;
use YlsIdeas\FeatureFlags\Manager;

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

    public function test_it_can_be_initialised(): void
    {
        $manager = new Manager($this->container, \Mockery::mock(Dispatcher::class));

        $this->assertInstanceOf(Manager::class, $manager);
    }

    public function test_it_can_check_if_features_are_accessible(): void
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
                'driver' => 'null',
            ]);

        $dispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(FeatureAccessing::class))
            ->once();

        $dispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(FeatureAccessed::class))
            ->once();

        $manager = new Manager($this->container, $dispatcher);
        $manager->extend('null', fn () => \Mockery::mock(Gateway::class)
            ->shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(true)
            ->getMock());

        $this->assertTrue($manager->accessible('my-feature'));
    }

    public function test_it_can_turn_on_features(): void
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
                'driver' => 'null',
            ]);

        $dispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(FeatureSwitchedOn::class))
            ->once();

        $manager = new Manager($this->container, $dispatcher);
        $manager->extend('null', fn () => \Mockery::mock(Gateway::class, Toggleable::class)
            ->shouldReceive('turnOn')
            ->with('my-feature')
            ->getMock());

        $manager->turnOn('test', 'my-feature');
    }

    public function test_it_can_turn_off_features(): void
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
                'driver' => 'null',
            ]);

        $dispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(FeatureSwitchedOff::class))
            ->once();

        $manager = new Manager($this->container, $dispatcher);
        $manager->extend('null', fn () => \Mockery::mock(Gateway::class, Toggleable::class)
            ->shouldReceive('turnOff')
            ->with('my-feature')
            ->getMock());

        $manager->turnOff('test', 'my-feature');
    }

    /**
     * @dataProvider services
     */
    public function test_it_can_flag_parts_of_the_package_to_be_turned_off($item): void
    {
        $manager = new Manager($this->container, \Mockery::mock(Dispatcher::class));

        $this->assertTrue($manager->{"uses$item"}());

        $manager->{"no$item"}();

        $this->assertFalse($manager->{"uses$item"}());
    }

    public function services(): array
    {
        return [
            ['Blade'],
            ['Validations'],
            ['Commands'],
            ['Scheduling'],
            ['Middlewares'],
        ];
    }
}
